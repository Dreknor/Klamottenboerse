<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Person;
use Illuminate\View\View;

/** Abbestellen der Info-Mails "Anmeldung möglich" per Link aus der Mail. */
class InfoMailsController extends Controller
{
    public function abbestellen(Person $person): View
    {
        $person->update(['info_mails_erlaubt_at' => null]);
        activity()->performedOn($person)->log('Info-Mails abbestellt');

        return view('public.hinweis', [
            'titel' => 'Abbestellt',
            'text' => 'Du bekommst keine Infos mehr zu künftigen Börsen. Du kannst dich trotzdem jederzeit auf der Website anmelden.',
        ]);
    }
}
