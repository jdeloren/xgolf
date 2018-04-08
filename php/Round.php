<?php

    class Round
    {
        private $pars = array();

        public function __construct() {
            $this->pars = [5, 4, 3, 5, 4, 4, 4, 4, 3];
        }

        public function getHole($hole) {
            return $this->pars[$hole];
        }
    }