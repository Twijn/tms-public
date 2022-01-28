<?php
    require_once("../internal/connect.php");

    $getStats = $con->prepare("SELECT * from homepage__stats where last_updated > date_sub(now(), interval 5 minute);");
    $getStats->execute();

    $streamers;
    $moderators;
    $users;

    $whosinvolved = "";

    function formatBMK($val) {
        if ($val >= 1000000000) {
            return number_format($val / 1000000000, 1) . "B";
        } else if ($val >= 1000000) {
            return number_format($val / 1000000, 1) . "M";
        } else if ($val >= 1000) {
            return number_format($val / 1000, 1) . "K";
        }
        return number_format($val, 1);
    }

    if ($getStats->rowCount() > 0) {
        $stats = $getStats->fetch(PDO::FETCH_ASSOC);

        $streamers = $stats["streamers"];
        $moderators = $stats["moderators"];
        $users = $stats["users"];
    } else {
        $con->prepare("truncate homepage__stats")->execute();

        $getStreamers = $con->prepare("SELECT distinct modfor_id from identity__moderator where active = true;");
        $getStreamers->execute();
        $streamers = $getStreamers->rowCount();

        $getModerators = $con->prepare("SELECT distinct identity_id from identity__moderator where active = true");
        $getModerators->execute();
        $moderators = $getModerators->rowCount();

        $getUsers = $con->prepare("SELECT count(id) as usercount from twitch__user;");
        $getUsers->execute();
        $users = $getUsers->fetch(PDO::FETCH_ASSOC)["usercount"];

        $con->prepare("INSERT into homepage__stats (streamers, moderators, users) values (?, ?, ?);")->execute(array($streamers, $moderators, $users));
    }

    $live = array();
    $getLive = $con->prepare("SELECT identity_id from live where end_time is null;");
    $getLive->execute();
    while ($row = $getLive->fetch(PDO::FETCH_ASSOC)) {
        array_push($live, $row["identity_id"]);
    }

    $getInvolved = $con->prepare("SELECT display_name, profile_image_url, follower_count, affiliation, identity_id from twitch__user where show_on_homepage = true order by rand() limit 6;");
    $getInvolved->execute();

    while ($row = $getInvolved->fetch(PDO::FETCH_ASSOC)) {
        $name = $row["display_name"];
        $pfp = $row["profile_image_url"];
        $formattedFollowers = formatBMK($row["follower_count"]);
        $followers = number_format($row["follower_count"], 0);
        $isPartner = $row["affiliation"] === "partner";

        $isLive = in_array($row["identity_id"], $live);

        $whosinvolved .= '<a href="https://twitch.tv/'.strtolower($name). '" class="object-link" target="__blank"><div class="twitch-user'.($isLive ? ' live' : '').'"><img src="'.$pfp.'" alt="Profile Picture for '.$name.'" /><span class="name">'.$name.($isPartner ? '&nbsp;<i class="fas fa-badge-check" aria-hidden="true"></i>' : '').'</span><span class="followers" title="'.$followers.'">'.$formattedFollowers.'</span></div></a>';
    }

    $users = number_format($users, 0);

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twitch Mod Squad</title>

    <!-- generated -->
    <link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="msapplication-TileColor" content="#a970ff">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
    <meta name="theme-color" content="#a970ff">
    <!-- -->

    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="page">
    <header>
        <div class="wrapper">
            <img src="assets/images/logo.webp" alt="Twitch Mod Squad Logo">

            <a href="#" aria-label="Toggle Menu" id="hamburger"><i class="far fa-bars"></i></a>

            <nav>
                <a href="#who-we-are">Who We Are</a><!--
                --><a href="#whos-involved">Who's Involved</a><!--
                --><a href="#hosting-services">Hosting Services</a><!--
                --><a href="#get-involved">Get Involved</a><!--
                --><a href="https://www.patreon.com/twitchmodsquad" class="highlight">Fund</a>
            </nav>
        </div>
    </header>

    <main>
        <section id="who-we-are">
            <h1>
                Twitch Mod Squad
            </h1>

            Twitch Mod Squad is a collection of Twitch and Discord communities built to ensure safety among our members. We provide tools, advice, and a larger community to <ul class="switch"><li><?= $streamers; ?> streamers</li><li><?= $moderators; ?> moderators</li><li><?= $users; ?> users</li></ul>.
        </section>

        <section id="whos-involved">
            <div class="wrapper">
                <h2>
                    Who's Involved
                </h2>

                <div>
                    <?= $whosinvolved; ?>
                </div>

                <button class="large-margin" type="button">View More Streamers</button>
            </div>
        </section>

        <section id="hosting-services">
            <div class="wrapper">
                <h2>
                    Hosting Services
                </h2>

                <p>
                    In addition to community benefits, Twitch Mod Squad also offers several hosting services for free to streamers and moderators.<br/>This can be used for an array of cases, including for Community servers or for private use.<br/>Contact <strong>Twijn#8888</strong> on Discord for more information.
                </p>

                <div class="servers">
                    <div class="server" id="game1"><div class="server-icon"><i class="fad fa-server"></i></div><span>Game 1<br/>Dallas, TX</span></div>
                    <div class="server" id="game2"><div class="server-icon"><i class="fad fa-server"></i></div><span>Game 2<br/>Dallas, TX</span></div>
                </div>

                <p>
                    <h3>Community Servers</h3>

                    <strong>Minecraft: </strong> mc.tmsqd.co
                </p>
            </div>
        </section>

        <section id="get-involved">
            <div class="wrapper">
                <h2>
                    Get Involved / Contact Us
                </h2>

                <p>Interested in joining Twitch Mod Squad, or interesting in sending a query for other comments, questions, or concerns? Fill out the form below.</p>

                <div id="form-response" style="display: none;"></div>

                <form id="contact-us">
                    <input type="text" name="contact-info" id="contact-info" maxlength="40" placeholder="Discord or Email Address (Twijn#8888 or twijn@twijn.net format)" />
                    <input type="text" name="subject" id="subject" maxlength="50" placeholder="Subject" />
                    
                    <textarea name="body" id="body" cols="30" rows="10" placeholder="Type your message body here..."></textarea>

                    <div class="g-recaptcha" data-sitekey="6LfqqxEeAAAAAMwo6OMlafkxvxV91BNB8lvZ2iH-" data-theme="dark"></div>

                    <button class="large-margin" type="submit">Submit</button>
                </form>
            </div>
        </section>

    </main>
    
    <footer>Twitch Mod Squad is not affiliated with Twitch Interactive Inc.</footer>
    </div>

    <script src="assets/js/jquery.js"> </script>
    <script src="assets/js/master.js"> </script>
    <script src="https://kit.fontawesome.com/107bb78db8.js" crossorigin="anonymous"> </script>
    <script src="https://www.google.com/recaptcha/api.js" async defer> </script>
</body>
</html>