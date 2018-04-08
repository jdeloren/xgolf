<?php
    require_once 'Player.php';
    require_once 'Team.php';

    class League
    {
        private $players;
        private $teams;

        private static function getTestPlayers() {
            return [
                new Player(0, 'Jamison DeLorenzo', 8),
                new Player(1, 'David Gurak', 11),
                new Player(2, 'Bejan Shemirani', 3),
                new Player(3, 'Rui Amorim', 7),
                new Player(4, 'Carlos Pavia', 14),
                new Player(5, 'Karl Steenburgh', 16),
                new Player(6, 'Andrew McLean', 4),
                new Player(7, 'Chris Knowlton', 18),
            ];
        }

        private static function getTestTeams() {
            $players = League::getTestPlayers();

            return [
                new Team(0, $players[0], $players[1]),
                new Team(1, $players[2], $players[3]),
                new Team(2, $players[4], $players[5]),
                new Team(3, $players[6], $players[7])
            ];
        }

        public function getPlayers() {
            return $this->players;
        }

        public function getTeams() {
            return $this->teams;
        }

        public function __construct() {
            $this->players = League::getTestPlayers();
            $this->teams = League::getTestTeams();
        }
    }