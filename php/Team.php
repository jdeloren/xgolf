<?php
    define(TEAN1, ['J. DeLorenzo']);

    class Team
    {
        /**
         * @var Player
         */
        private $player1;
        /**
         * @var Player
         */
        private $player2;

        private $id;

        public function __construct($id, $p1, $p2) {
            $this->id = $id;
            $this->player1 = $p1;
            $this->player2 = $p2;
        }

        public function id() {
            return $this->id;
        }

        public function name() {
            return $this->player1->name();
        }

        public function getAFlight() {
            return $this->player1;
        }

        public function getBFlight() {
            return $this->player2;
        }
    }