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
            'name' => 'required',
            'betreff' => 'required',
            'text'  => 'required'
        ];
    }

    public function authorize() {
        return true;
    }
}