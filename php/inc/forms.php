<?php

    function getGolferHoleInputData($playerID, $holeID) {
        $golfer = 'golfer' . $playerID;
        $hole = 'hole' . $holeID;

        switch($playerID) {
            case 1:
                $opponentID = 3;
                break;
            case 2:
                $opponentID = 4;
                break;
            case 3:
                $opponentID = 1;
                break;
            case 4:
                $opponentID = 2;
                break;
        }

        $current = 'golfer' . $playerID . '-' . $hole;
        $opposite = 'golfer' . $opponentID;

        return 'id="' . $current . '" data-gid="' . $golfer . '" data-hid="' . $hole . '" data-oid="' . $opposite . '"';
    }