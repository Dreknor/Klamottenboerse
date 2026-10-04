<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ordner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/** Team-Ablage: Ordner und Dateien (ersetzt die Nextcloud). */
class AblageController extends Controller
{
    public function index(?Ordner $ordner = null): View
    {
        return view('admin.ablage.index', [
            'ordner' => $ordner,
            'pfad' => $ordner?->pfad() ?? collect(),
            'unterordner' => Ordner::query()->where('parent_id', $ordner?->id)->withCount(['kinder', 'media'])->orderBy('name')->get(),
            'dateien' => $ordner ? $ordner->getMedia('dateien')->sortByDesc('created_at') : collect(),
        ]);
    }

    public function ordnerAnlegen(Request $request, ?Ordner $ordner = null): RedirectResponse
    {
        $daten = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('ordner', 'name')->where('parent_id', $ordner?->id)],
        ], ['name.unique' => 'Diesen Ordner gibt es hier schon.']);

        $neu = Ordner::create(['name' => $daten['name'], 'parent_id' => $ordner?->id]);

        return redirect()->route('admin.ablage.ordner', $neu)->with('erfolg', 'Ordner angelegt.');
    }

    public function ordnerAendern(Request $request, Ordner $ordner): RedirectResponse
    {
        $daten = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('ordner', 'name')->where('parent_id', $ordner->parent_id)->ignore($ordner)],
            'fuer_helfer' => ['nullable', 'boolean'],
        ]);
        $ordner->update(['name' => $daten['name'], 'fuer_helfer' => $request->boolean('fuer_helfer')]);

        return back()->with('erfolg', 'Ordner gespeichert.');
    }

    public function ordnerLoeschen(Ordner $ordner): RedirectResponse
    {
        if ($ordner->kinder()->exists() || $ordner->media()->exists()) {
            return back()->with('fehler', 'Der Ordner ist nicht leer. Bitte zuerst Dateien und Unterordner löschen oder verschieben.');
        }
        $ziel = $ordner->parent_id;
        $ordner->delete();

        return $ziel ? redirect()->route('admin.ablage.ordner', $ziel) : redirect()->route('admin.ablage.index');
    }

    public function hochladen(Request $request, Ordner $ordner): RedirectResponse
    {
        $request->validate([
            'dateien' => ['required', 'array', 'max:30'],
            'dateien.*' => ['file', 'max:'.Ordner::MAX_KB, 'mimes:jpg,jpeg,png,gif,webp,heic,pdf,doc,docx,odt,xls,xlsx,ods,txt,md,csv'],
        ], ['dateien.*.max' => 'Eine Datei ist größer als 20 MB.', 'dateien.*.mimes' => 'Dieser Dateityp wird nicht unterstützt.']);

        foreach ($request->file('dateien') as $datei) {
            $ordner->addMedia($datei)
                ->withCustomProperties(['hochgeladen_von' => $request->user()->name])
                ->toMediaCollection('dateien');
        }

        return back()->with('erfolg', count($request->file('dateien')).' Datei(en) hochgeladen.');
    }

    /** Datei anzeigen (Bilder, PDF im Browser) oder herunterladen. */
    public function datei(Request $request, Media $media): BinaryFileResponse
    {
        abort_unless($media->model_type === (new Ordner)->getMorphClass(), 404);

        return self::ausliefern($media, $request->boolean('vorschau'), $request->boolean('download'));
    }

    public function dateiLoeschen(Media $media): RedirectResponse
    {
        abort_unless($media->model_type === (new Ordner)->getMorphClass(), 404);
        $media->delete();

        return back()->with('erfolg', 'Datei gelöscht.');
    }

    public static function ausliefern(Media $media, bool $vorschau = false, bool $download = false): BinaryFileResponse
    {
        $pfad = $vorschau && $media->hasGeneratedConversion('vorschau') ? $media->getPath('vorschau') : $media->getPath();
        $art = $download ? 'attachment' : 'inline';

        return response()->file($pfad, [
            'Content-Disposition' => $art.'; filename="'.addslashes($media->file_name).'"',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
