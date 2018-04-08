<?php
    $select = $_POST['select'];
    $value = $_POST['team'];

    require_once '../League.php';
    $league = new League();
    //echo $team;

    /** @var Team $team */
    $team = $league->getTeams()[$value];

    $a = $team->getAFlight();
    $b = $team->getBFlight();

    $encode = array(
        'team-name' => $select,
        'golfer1-name'=> $a->name(), 'golfer1-handicap' => $a->handicap(),
        'golfer2-name'=> $b->name(), 'golfer2-handicap' => $b->handicap());

    echo json_encode(array_values($encode));
    ?>