<?php
error_reporting(E_ALL);
?>
<html>
    <head>
        <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
        <title>XGolf Scoresheet</title>

        <link rel="stylesheet" href="css/scoresheet.css"/>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

        <script>
            class Round {
                constructor(pars, handicaps) {
                    this.pars = pars;
                    this.handicaps = handicaps;
                }
            }

            function generateHandicapDiff(gap) {
                let handicaps = JSON.parse(sessionStorage.getItem('round')).handicaps;
                let mod = gap < 0 ? -1 : 1;

                gap = Math.abs(gap);

                let div = Math.floor(gap / 9) * mod;
                let rem = gap % 9;
                let hole = 0;

                let gaps = new Array(9);
                gaps.fill(div);

                console.log("DIV: " + div);
                console.log("REM: " + rem);
                console.log("PRE ADJUST: " + gaps);

                for (let i = 0; i < rem; ++i) {
                    hole = handicaps.indexOf((9-i).toString());
                    gaps[parseInt(hole)] += mod;
                }

                return gaps;
            }

            function initTeamSelector() {
                $("select[name^='team']").click( function() {
                    $.ajax({
                        url: 'ajax/teamselect.php',
                        data: {
                            select: $(this).attr('name'),
                            team: $(this).val()
                        },
                        error: function() {
                            $('#info').html('<p>An error has occurred</p>');
                        },
                        type: "POST",
                        dataType : 'json',
                        success: function(response) {
                            let values = String(response).split(',');

                            if (values[0] === 'team1') {
                                $('#golfer1-name').val(values[1]);
                                $('#golfer1-handicap').val(values[2]);
                                $('#golfer2-name').val(values[3]);
                                $('#golfer2-handicap').val(values[4]);
                            }
                            else if (values[0] === 'team2') {
                                $('#golfer3-name').val(values[1]);
                                $('#golfer3-handicap').val(values[2]);
                                $('#golfer4-name').val(values[3]);
                                $('#golfer4-handicap').val(values[4]);
                            }
                        }
                    });
                });
            }

            function setPoints(score1, score2, field1, field2) {
                let i1 = parseInt(score1);
                let i2 = parseInt(score2);

                if (i1 < i2) {
                    field1.val(2);
                    field2.val(0);
                }
                else if (i1 > i2) {
                    field1.val(0);
                    field2.val(2);
                }
                else {
                    field1.val(1);
                    field2.val(1);
                }
            }

            function initRoundCalculator() {
                $('.hole').bind("change", function() {
                    // update individual round score
                    let id = $(this).attr('data-gid');
                    let score = 0;

                    $('input[id^="' + id + '-hole"]').each(function(i, obj) {
                        score += parseInt($(this).val());
                    });

                    $('#' + id + '-round').val(score);

                    let player = $(this).attr('id');
                    let hole = $(this).attr('data-hid');
                    let oid = $(this).attr('data-oid');
                    let opponent = oid + '-' + hole;

                    // calculate handicaps
                    let c1 = parseInt($('#' + id + "-handicap").val());
                    let c2 = parseInt($('#' + oid + "-handicap").val());

                    let hdiff = generateHandicapDiff(c1 - c2);

                    console.log("HANDICAP ADJUST: " + hdiff);

                    // update matchup points

                    let flight, team;

                    if (id.indexOf('1') !== -1 || id.indexOf('3') !== -1) {
                        flight = 0;
                    }
                    else {
                        flight = 1;
                    }

                    if (id.indexOf('1') !== -1 || id.indexOf('2') !== -1) {
                        team = 0;
                    }
                    else {
                        team = 1;
                    }

                    let s1 = $('#team1-' + hole + '-a');
                    let s2 = $('#team1-' + hole + '-b');
                    let s3 = $('#team1-' + hole + '-t');
                    let s4 = $('#team2-' + hole + '-a');
                    let s5 = $('#team2-' + hole + '-b');
                    let s6 = $('#team2-' + hole + '-t');

                    let h1, h2 = '';

                    switch(flight) {
                        case 0:
                            h1 = s4;
                            h2 = s1;
                            break;
                        case 1:
                            h1 = s5;
                            h2 = s2;
                            break;
                    }

                    // individual points
                    let i = hole.substr(hole.length - 1) - 1;
                    let f1 = parseInt($('#' + player).val());
                    let f2 = parseInt($('#' + opponent).val());

                    f2 += hdiff[i];

                    console.log("P1 " + player + "> " + f1);
                    console.log("P2 " + opponent + "> " + f2);

                    setPoints(f1, f2, h1, h2);

                    // team points
                    h1 = s1.val() + s2.val();
                    h2 = s4.val() + s5.val();

                    setPoints(h1, h2, s3, s6);
                });
            }

            window.onload = function() {
                initTeamSelector();
                initRoundCalculator();

                let pars = [];
                let handicaps = [];

                let i = 0;
                $('.pars').each(function (index, value) { pars[i++] = $(this).val(); } );
                i = 0;
                $('.handicaps').each(function (index, value) { handicaps[i++] = $(this).val(); } );

                let round = new Round(pars, handicaps);

                console.log("ROUND SET: " + round);

                sessionStorage.setItem('round', JSON.stringify(round));
            };
        </script>

        <script>
            $(function() {
                //$( "#team1" ).selectmenu();
                //$( "#team2" ).selectmenu();
            });
        </script>
    </head>
    <body>
        <?php
            require_once 'League.php';
            require_once 'inc/forms.php';
            $league = new League();
        ?>
        <form action="submit.php">
            <div class="card">
                <div class="description">
                    <input type="text" name="week" title="Week Number" value="1">
                    <input type="text" name="course" title="Round Location" value="Shadow Lake - Back" readonly>
                </div>
                <div class="heading">
                    <input type="text" class="label" name="par" value="PAR" title="Hole 1" readonly>
                    <input type="text" class="pars" name="hole1-par" title="Hole 1" value="4" data-id="hole1" readonly>
                    <input type="text" class="pars" name="hole2-par" title="Hole 2" value="4" data-id="hole2" readonly>
                    <input type="text" class="pars" name="hole3-par" title="Hole 3" value="4" data-id="hole3" readonly>
                    <input type="text" class="pars" name="hole4-par" title="Hole 4" value="4" data-id="hole4" readonly>
                    <input type="text" class="pars" name="hole5-par" title="Hole 5" value="4" data-id="hole5" readonly>
                    <input type="text" class="pars" name="hole6-par" title="Hole 6" value="4" data-id="hole6" readonly>
                    <input type="text" class="pars" name="hole7-par" title="Hole 7" value="4" data-id="hole7" readonly>
                    <input type="text" class="pars" name="hole8-par" title="Hole 8" value="4" data-id="hole8" readonly>
                    <input type="text" class="pars" name="hole9-par" title="Hole 9" value="4" data-id="hole9" readonly>
                </div>
                <div class="heading">
                    <input type="text" class="label" name="handicap" value="HANDICAP" title="Hole 1" readonly>
                    <input type="text" class="handicaps" name="hole1-hdcp" title="Hole 1" value="5" data-id="hole1" readonly>
                    <input type="text" class="handicaps" name="hole2-hdcp" title="Hole 2" value="2" data-id="hole2" readonly>
                    <input type="text" class="handicaps" name="hole3-hdcp" title="Hole 3" value="4" data-id="hole3" readonly>
                    <input type="text" class="handicaps" name="hole4-hdcp" title="Hole 4" value="9" data-id="hole4" readonly>
                    <input type="text" class="handicaps" name="hole5-hdcp" title="Hole 5" value="1" data-id="hole5" readonly>
                    <input type="text" class="handicaps" name="hole6-hdcp" title="Hole 6" value="3" data-id="hole6" readonly>
                    <input type="text" class="handicaps" name="hole7-hdcp" title="Hole 7" value="6" data-id="hole7" readonly>
                    <input type="text" class="handicaps" name="hole8-hdcp" title="Hole 8" value="7" data-id="hole8" readonly>
                    <input type="text" class="handicaps" name="hole9-hdcp" title="Hole 9" value="8" data-id="hole9" readonly>
                </div>
                <div class="golfer">
                    <input type="text" id="golfer1-name" class="name" value="" data-gid="golfer1">
                    <input type="text" id="golfer1-handicap" class="handicap" value="" data-gid="golfer1">
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(1, 1) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(1, 2) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(1, 3) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(1, 4) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(1, 5) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(1, 6) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(1, 7) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(1, 8) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(1, 9) ?> >
                    <input type="text" class="calc" id="golfer1-round" name="total" value="42" data-gid="golfer1">
                    <input type="text" class="calc" id="golfer1-net" name="net" value="34" data-gid="golfer1">
                    <input type="text" class="calc" id="golfer1-esc" name="esc" value="34" data-gid="golfer1">
                    <input type="text" class="calc" id="golfer1-points" name="points" value="9" data-gid="golfer1">
                </div>
                <div class="golfer">
                    <input type="text" id="golfer2-name" class="name" value="" data-gid="golfer2">
                    <input type="text" id="golfer2-handicap" class="handicap" value="" data-gid="golfer2">
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(2, 1) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(2, 2) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(2, 3) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(2, 4) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(2, 5) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(2, 6) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(2, 7) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(2, 8) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(2, 9) ?> >
                    <input type="text" class="calc" id="golfer2-round" name="total" value="48" data-gid="golfer2">
                    <input type="text" class="calc" id="golfer2-net" name="net" value="37" data-gid="golfer2">
                    <input type="text" class="calc" id="golfer2-esc" name="esc" value="34" data-gid="golfer2">
                    <input type="text" class="calc" id="golfer2-points" name="points" value="9" data-gid="golfer2">
                </div>
                <div class="team">
                    <select name="team1" id="team1" class="name" title="Team 1 selector" data-team="team1">
                        <?php
                            /** @var Team $team */
                            foreach ($league->getTeams() as $team) {
                                echo '<option value="' . $team->id() . '">' . $team->name() . '</option>';
                            }
                        ?>
                    </select>
                    <input type="text" class="handicap" id="team1-handicap" value="20" readonly>
                    <input type="text" class="hole" id="team1-hole1-a" value="0" readonly>
                    <input type="text" class="hole" id="team1-hole1-b" value="0" readonly>
                    <input type="text" class="hole" id="team1-hole1-t" value="0" readonly>
                    <input type="text" class="hole" id="team1-hole2-a" value="0" readonly>
                    <input type="text" class="hole" id="team1-hole2-b" value="0" readonly>
                    <input type="text" class="hole" id="team1-hole2-t" value="0" readonly>
                    <input type="text" class="hole" id="team1-hole3-a" value="0" readonly>
                    <input type="text" class="hole" id="team1-hole3-b" value="0" readonly>
                    <input type="text" class="hole" id="team1-hole3-t" value="0" readonly>
                    <input type="text" class="hole" id="team1-hole4-a" value="0" readonly>
                    <input type="text" class="hole" id="team1-hole4-b" value="0" readonly>
                    <input type="text" class="hole" id="team1-hole4-t" value="0" readonly>
                    <input type="text" class="hole" id="team1-hole5-a" value="0" readonly>
                    <input type="text" class="hole" id="team1-hole5-b" value="0" readonly>
                    <input type="text" class="hole" id="team1-hole5-t" value="0" readonly>
                    <input type="text" class="hole" id="team1-hole6-a" value="0" readonly>
                    <input type="text" class="hole" id="team1-hole6-b" value="0" readonly>
                    <input type="text" class="hole" id="team1-hole6-t" value="0" readonly>
                    <input type="text" class="hole" id="team1-hole7-a" value="0" readonly>
                    <input type="text" class="hole" id="team1-hole7-b" value="0" readonly>
                    <input type="text" class="hole" id="team1-hole7-t" value="0" readonly>
                    <input type="text" class="hole" id="team1-hole8-a" value="0" readonly>
                    <input type="text" class="hole" id="team1-hole8-b" value="0" readonly>
                    <input type="text" class="hole" id="team1-hole8-t" value="0" readonly>
                    <input type="text" class="hole" id="team1-hole9-a" value="0" readonly>
                    <input type="text" class="hole" id="team1-hole9-b" value="0" readonly>
                    <input type="text" class="hole" id="team1-hole9-t" value="0" readonly>
                </div>
                <div class="team">
                    <select name="team2" id="team2" class="name" title="Team 1 selector" data-team="team1">
                        <?php
                            /** @var Team $team */
                            foreach ($league->getTeams() as $team) {
                                echo '<option value="' . $team->id() . '">' . $team->name() . '</option>';
                            }
                        ?>
                    </select>
                    <input type="text" class="handicap" id="team2-handicap" value="4" readonly>
                    <input type="text" class="hole" id="team2-hole1-a" value="0" readonly>
                    <input type="text" class="hole" id="team2-hole1-b" value="0" readonly>
                    <input type="text" class="hole" id="team2-hole1-t" value="0" readonly>
                    <input type="text" class="hole" id="team2-hole2-a" value="0" readonly>
                    <input type="text" class="hole" id="team2-hole2-b" value="0" readonly>
                    <input type="text" class="hole" id="team2-hole2-t" value="0" readonly>
                    <input type="text" class="hole" id="team2-hole3-a" value="0" readonly>
                    <input type="text" class="hole" id="team2-hole3-b" value="0" readonly>
                    <input type="text" class="hole" id="team2-hole3-t" value="0" readonly>
                    <input type="text" class="hole" id="team2-hole4-a" value="0" readonly>
                    <input type="text" class="hole" id="team2-hole4-b" value="0" readonly>
                    <input type="text" class="hole" id="team2-hole4-t" value="0" readonly>
                    <input type="text" class="hole" id="team2-hole5-a" value="0" readonly>
                    <input type="text" class="hole" id="team2-hole5-b" value="0" readonly>
                    <input type="text" class="hole" id="team2-hole5-t" value="0" readonly>
                    <input type="text" class="hole" id="team2-hole6-a" value="0" readonly>
                    <input type="text" class="hole" id="team2-hole6-b" value="0" readonly>
                    <input type="text" class="hole" id="team2-hole6-t" value="0" readonly>
                    <input type="text" class="hole" id="team2-hole7-a" value="0" readonly>
                    <input type="text" class="hole" id="team2-hole7-b" value="0" readonly>
                    <input type="text" class="hole" id="team2-hole7-t" value="0" readonly>
                    <input type="text" class="hole" id="team2-hole8-a" value="0" readonly>
                    <input type="text" class="hole" id="team2-hole8-b" value="0" readonly>
                    <input type="text" class="hole" id="team2-hole8-t" value="0" readonly>
                    <input type="text" class="hole" id="team2-hole9-a" value="0" readonly>
                    <input type="text" class="hole" id="team2-hole9-b" value="0" readonly>
                    <input type="text" class="hole" id="team2-hole9-t" value="0" readonly>
                </div>
                <div class="golfer">
                    <input type="text" id="golfer3-name" class="name" value="" data-gid="golfer3">
                    <input type="text" id="golfer3-handicap" class="handicap" value="" data-gid="golfer3">
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(3, 1) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(3, 2) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(3, 3) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(3, 4) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(3, 5) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(3, 6) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(3, 7) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(3, 8) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(3, 9) ?> >
                    <input type="text" class="calc" id="golfer3-round" name="total" value="38" data-gid="golfer3">
                    <input type="text" class="calc" id="golfer3-net" name="net" value="34" data-gid="golfer3">
                    <input type="text" class="calc" id="golfer3-esc" name="esc" value="34" data-gid="golfer3">
                    <input type="text" class="calc" id="golfer3-points" name="points" value="9" data-gid="golfer3">
                </div>
                <div class="golfer">
                    <input type="text" id="golfer4-name" class="name" value="" data-gid="golfer4">
                    <input type="text" id="golfer4-handicap" class="handicap" value="" data-gid="golfer4">
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(4, 1) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(4, 2) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(4, 3) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(4, 4) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(4, 5) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(4, 6) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(4, 7) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(4, 8) ?> >
                    <input type="text" class="hole" value="0" <?= getGolferHoleInputData(4, 9) ?> >
                    <input type="text" class="calc" id="golfer4-round" name="total" value="34" data-gid="golfer4">
                    <input type="text" class="calc" id="golfer4-net" name="net" value="34" data-gid="golfer4">
                    <input type="text" class="calc" id="golfer4-esc" name="esc" value="34" data-gid="golfer4">
                    <input type="text" class="calc" id="golfer4-points" name="points" value="9" data-gid="golfer4">
                </div>

                <div class="actionbar">
                    <button name="Calculate" action="ajaxScorer.php">Calculate</button>
                    <button name="Submit" action="ajaxUpdate.php">Submit</button>
                </div>
            </div>
        </form>
    </body>
</html>