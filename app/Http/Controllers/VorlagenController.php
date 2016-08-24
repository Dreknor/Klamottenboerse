<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 22.08.2016
 * Time: 21:19
 */

namespace App\Http\Controllers;


use App\Http\Requests\MailvorlagenAnlegenRequest;
use App\Repositories\Mailvorlagen\VorlagenRepository;
use Illuminate\Http\Request;
use App\Models\Mailvorlagen\Mailvorlagen;

class VorlagenController extends Controller
{
    public function __construct(VorlagenRepository $vorlagenRepository){
        $this->VorlagenRepository=$vorlagenRepository;
    }

    public function index(){
        return view('mailvorlagen.mailvorlagen',[
          "Vorlagen" => $this->VorlagenRepository->alle()
        ]);
    }

    public function deleteVorlage(Request $request){

        $Vorlage=$this->VorlagenRepository->find($request->input('VorlagenID'));
        $Vorlage->delete();
        return redirect()->back()->with(["Message" => "Vorlage erfolgreich gelöscht", "Type" => "success"]);
    }

    public function storeVorlage(MailvorlagenAnlegenRequest $request){

        Mailvorlagen::create($request->all());
        return redirect(url('/Mailvorlagen'))->with(["Message"=> "Vorlage erstellt", "type" => "success"]);

    }

    public function edit(MailvorlagenAnlegenRequest $request){
        $Mailvorlage=$this->VorlagenRepository->find($request->VorlagenID);
        $Mailvorlage->name=$request->name;
        $Mailvorlage->betreff=$request->betreff;
        $Mailvorlage->text=$request->text;

        $Mailvorlage->save();

        return redirect(url('/Mailvorlagen'))->with(["Message"=> "Vorlage gespeichert.", "type" => "success"]);


    }
}