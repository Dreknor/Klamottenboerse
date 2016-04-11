<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 30.03.2016
 * Time: 16:23
 */

namespace App\Http\Requests;


class InteressentenAnlegenRequest extends Request
{
    public function rules() {
        return [
            'nachname' => 'required|alpha|min:3',
            'mail' => 'required|email|unique:interessenten,mail'
        ];
    }

    public function authorize() {
        return true;
    }
}