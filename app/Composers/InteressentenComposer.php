<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 23.03.2016
 * Time: 19:25
 */

namespace App\Composers;


use App\Repositories\Interessenten\InteressentenRepository;
use Illuminate\Contracts\View\View;

class InteressentenComposer
{
    public function __construct(InteressentenRepository $interessentenRepository)
    {
        $this->interessentenRepository = $interessentenRepository;
    }

    public function compose(View $view) {

        $view->with('InteressentenCount', $this->interessentenRepository->countAll());
        $view->with('MitarbeiterCount', $this->interessentenRepository->countMitarbeiter());
        $view->with('KinderhausCount', $this->interessentenRepository->countKinderhaus());
    }
}
