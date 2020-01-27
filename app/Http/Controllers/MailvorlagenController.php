<?php

namespace App\Http\Controllers;

use App\Http\Requests\MailvorlagenEditRequest;
use App\Model\Mailvorlagen;
use Illuminate\Http\Request;

class MailvorlagenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('mails.vorlagen.index',[
            "Vorlagen"  => Mailvorlagen::all()
            ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $mailvorlagen = new Mailvorlagen($request->all());
        $mailvorlagen->betreff = "";
        $mailvorlagen->text = "";

        $mailvorlagen->save();

        return redirect(url('mailvorlagen/'.$mailvorlagen->id."/edit"))->with([
           "success"    => "Mailvorlage gespeichert"
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\mailvorlagen  $mailvorlagen
     * @return \Illuminate\Http\Response
     */
    public function show(mailvorlagen $mailvorlagen)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\mailvorlagen  $mailvorlagen
     * @return \Illuminate\Http\Response
     */
    public function edit(mailvorlagen $mailvorlagen)
    {
        return view('mails.vorlagen.edit',[
            "Vorlage"   => $mailvorlagen
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\mailvorlagen  $mailvorlagen
     * @return \Illuminate\Http\Response
     */
    public function update(MailvorlagenEditRequest $request, mailvorlagen $mailvorlagen)
    {
        $mailvorlagen->fill($request->all());
        $mailvorlagen->save();

        return redirect(url('mailvorlagen'))->with([
            "success"   => "Vorlage gespeichert."
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\mailvorlagen  $mailvorlagen
     * @return \Illuminate\Http\Response
     */
    public function destroy(mailvorlagen $mailvorlagen)
    {
        $mailvorlagen->delete();
        return redirect(url('mailvorlagen'))->with([
            "success"   => "Vorlage gelöscht."
        ]);
    }
}
