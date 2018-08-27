<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 30.03.2016
 * Time: 16:23
 */

namespace App\Http\Requests;


class MailvorlagenAnlegenRequest extends Request
{
    public function rules() {
        return [
            'name' => 'required|min:5',
            'betreff' => 'required|min:15',
            'text'  => 'required|min:15'
        ];
    }

    public function authorize() {
        return true;
    }
}