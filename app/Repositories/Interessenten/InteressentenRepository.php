<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 23.03.2016
 * Time: 19:05
 */

namespace App\Repositories\Interessenten;

use App\Models\Interessenten\Interessenten;

    class InteressentenRepository
    {
        public function all() {
            return \App\Models\Interessenten\Interessenten::query()->get();
        }

        public function Mitarbeiter() {
            return \App\Models\Interessenten\Interessenten::query()->where('mitarbeiter', 1)->get();
        }

        public function Kinderhaus() {
            return \App\Models\Interessenten\Interessenten::query()->where('kinderhaus', 1)->get();
        }

        /*
         * @return int
         */
        public function countAll() {
            return \App\Models\Interessenten\Interessenten::query()->count();
        }

        /*
         * @return int
         */
        public function countMitarbeiter() {
            return \App\Models\Interessenten\Interessenten::query()->where('mitarbeiter', 1)->count();
        }

        /*
         * @return int
         */
        public function countKinderhaus() {
            return \App\Models\Interessenten\Interessenten::query()->where('kinderhaus', 1)->count();
        }

        /*
         * @return
         */
        public function search($string) {
            return \App\Models\Interessenten\Interessenten::query()
                ->where('nachname', 'LIKE','%'.$string.'%')
                ->orWhere('vorname', 'Like','%'.$string.'%')
                ->get();
        }

        static public function findInteressent($id){
            $Interessent= Interessenten::query()->findOrFail($id);
            $Interessent->nachrichten=$Interessent->nachrichten()->paginate(10);
            return $Interessent;
        }
               
  
    }
