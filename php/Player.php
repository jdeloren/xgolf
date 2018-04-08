<?php
    class Player
    {
        private $name;
        private $handicap;

        public function __construct($id, $name, $handicap) {
            $this->id = $id;
            $this->name = $name;
            $this->handicap = $handicap;
        }

        public function name() {
            $exploded = explode(' ', $this->name);
            return $exploded[0][0] . '. ' . $exploded[1];
        }

        public function getLastName() {
            return explode(' ', $this->name)[1];
        }

        public function handicap() {
            return $this->handicap;
        }

    }
