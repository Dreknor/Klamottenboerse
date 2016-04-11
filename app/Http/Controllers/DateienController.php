<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 03.04.2016
 * Time: 08:21
 */

namespace App\Http\Controllers;



use App\Http\Requests\Request;
use App\Models\Dateien\Dateien;
use App\Repositories\Dateien\DateienRepository;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Response;

class DateienController extends Controller
{
    public function __construct(DateienRepository $dateienRepository)
    {
        $this->middleware('auth');
        $this->dateienRepository = $dateienRepository;

    }

    public function index()
    {
        return view('klamottenboerse.dateien', [
            "Dateien" => $this->dateienRepository->all()
        ]);
    }

    public function uploadFiles()
    {

        // GET ALL THE INPUT DATA , $_GET,$_POST,$_FILES.
        $file = Input::file('file');

        // SET UPLOAD PATH
        $destinationPath ='\app\anhaenge';
        // GET THE FILE EXTENSION
        $extension = $file->getClientOriginalExtension();
        // RENAME THE UPLOAD WITH RANDOM NUMBER
        $fileName = $file->getClientOriginalName();
        $pfad = date('Y_m_d').'_'.$file->getClientOriginalName();
        // MOVE THE UPLOADED FILES TO THE DESTINATION DIRECTORY
        $upload_success = $file->move( storage_path().$destinationPath, $pfad);

        $Datei=Dateien::firstOrCreate([
            'dateiname' => $fileName
        ]);
        
        $Datei->pfad = $pfad;
        $Datei->mime = $file->getClientMimeType();
        $Datei->save();
        
        

        // IF UPLOAD IS SUCCESSFUL SEND SUCCESS MESSAGE OTHERWISE SEND ERROR MESSAGE
        if ($upload_success) {
            return Redirect::to('/Dateien')->with('message', 'Upload successfully');

        }
    }

    public function destroy($id) {
        $Datei=$this->dateienRepository->findDatei($id);
        $Datei->delete();
        
        return redirect('/Dateien');
    }

    public function get($id){

        $Datei = $this->dateienRepository->findDatei($id);
        $file = Storage::disk('anhang')->get($Datei->pfad);
        $pfad=storage_path('app\anhaenge\\'.$Datei->pfad);


       /* return (new \Illuminate\Http\Response($file, 200))
            ->header('Content-Type', $Datei->mime);
        */
        return response()->download($pfad, $Datei->dateiname,['Content-Type' => $Datei->mime]);
    }
}