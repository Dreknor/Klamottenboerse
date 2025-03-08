<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 28.08.2018
 * Time: 10:06
 */

namespace App\Http\ViewComposers;

use App\Model\Interessenten;
use App\Repositories\Interessenten\InteressentenRepository;
use App\Repositories\Mails\ImapRepository;
use Illuminate\View\View;

class AppComposer
{
    protected $imapRepository;
    public function __construct(ImapRepository $imapRepository)
    {
        $this->imapRepository = $imapRepository;
    }

    public function compose(View $view)
    {
        //$view->with('unreadMail', $this->imapRepository->unseenMessages($this->imapRepository->connect()));
    }
}
