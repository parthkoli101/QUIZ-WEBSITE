<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRACKQUIZ</title>
    <link rel="stylesheet" href="style.css">

    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

   
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>
<body class="proj">
    
    <div id="main">
    
    <header>
        <nav class="mainnav">
            <div class="nav_left">
                <button class="crackdrop"><img src="https://uploads.turbologo.com/uploads/design/preview_image/61269529/watermark_preview_image20240831-1-1exmw04.png" alt=""></button>
                
            </div>
            <div class="nav_mid">
                <a href="#aboutus" class="nav_link">About Us</a>
                <a href="feedback.php" class="nav_link">Feedback</a>
                <a href="#q" class="nav_link">Games</a>
                <a href="#learn" class="nav_link">Learning</a>
            </div>
            <div class="nav_right">
            <?php
            
            if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
                
                echo '<p style="visibility: hidden;">-------</p>';
                echo '<a href="signin.html" class="nav_log">Signin</a><h4>|</h4>';
                echo '<a href="login.html" class="nav_log">Login</a>';
            }
            else{
                echo '<p style="visibility: hidden;">----------------------</p>';
            }
            ?>
                <a href="profile.php"><button id="profile"><i class="fa-regular fa-user"></i></button></a>
                
            </div>
        </nav>
    </header>
    <div class="search">
    <h1 class="head">CRACKQUIZ</h1>
    </div>
    <div class="content" id="q">
    <div class="quiz1" id="q1">
        <p>MONUMENTS QUIZ</p>
        <img src="https://thumbs.dreamstime.com/b/world-landmarks-collection-travel-tourism-vector-illustration-145255720.jpg" alt="">
        <a href="#instructions"><button class="btn2" id="startq1" style="width: 100%; height: 50px;">START QUIZ</button></a>
    </div>
    <div class="quiz2" id="q2">
        <p>ANIMALS QUIZ</p>
        <img src="https://i.pinimg.com/736x/5d/e1/2a/5de12adc88a9420e0b75400bd733a5a0--puzzle-art-jigsaw-puzzles.jpg" alt="">
        <a href="#instructions"><button class="btn2" id="startq2">START QUIZ</button></a>
       
    </div>
    <div class="quiz3" id="q3">
        <p>HISTORY QUIZ</p>
        <img src="https://cdn.soft112.com/history-quiz-game-multiplayer-ios/00/00/0G/2O/00000G2ORN/pad_screenshot.jpg" alt="">
        <a href="#instructions"><button class="btn2" id="startq3">START QUIZ</button></a>
    </div>
    <div class="quiz4" id="q4">
        <p>COUNTRY QUIZ</p>
        <img src="https://i.pinimg.com/originals/77/05/7b/77057b0ce3fe86731bb3d0f5cd1a37f9.jpg" alt="">
        <a href="#instructions"><button class="btn2" id="startq4">START QUIZ</button></a>
    </div>
    </div>
    <div class="content1">
        <div class="quiz5" id="q5">
            <p>BOARD GAMES QUIZ</p>
            <img src="https://static.displate.com/857x1200/displate/2022-07-26/3218c028d4542d74030df373e24db49b_cfc2f9699ddd759659cd4ccbf746b461.jpg" alt="">
            <a href="#instructions"><button class="btn2" id="startq5">START QUIZ</button></a>
        </div>
        <div class="quiz6" id="q6">
            <p>MARVEL QUIZ</p>
            <img src="https://i.redd.it/vrtyzwe444h21.jpg" alt="">
            <a href="#instructions"><button class="btn2" id="startq6">START QUIZ</button></a>
        </div>
        <div class="quiz7" id="q7">
            <p>GAMING QUIZ</p>
            <img src="https://static.displate.com/857x1200/displate/2020-06-07/6241ca833bcbfb28e762d38fd40e6a3c_25f794afdcc05e5a5ddad4b7c8e1dcfd.jpg" alt="">
            <a href="#instructions"><button class="btn2" id="startq7">START QUIZ</button></a>
        </div>
        <div class="quiz8" id="q8">
            <p>CAR QUIZ</p>
            <img src="https://assets.promptbase.com/DALLE_IMAGES/YucLxJhh0HVm9Vp7r5tr/resized/1678965973899_1000x1000.webp?alt=media&token=9efc18bf-f292-488d-9139-aecbd0ef228d" alt="">
            <a href="#instructions"><button class="btn2" id="startq8">START QUIZ</button></a>
        </div>
    </div>
    <div class="content2">
        <div class="quiz5" id="q9">
            <p>SPORTS QUIZ</p>
            <img src="https://is3-ssl.mzstatic.com/image/thumb/Purple5/v4/40/48/b2/4048b208-eedd-596d-dc85-af9ac0cb3c94/pr_source.png/750x750bb.jpeg" alt="">
            <a href="1.html"><button class="btn2">VIEW QUIZ TOPICS</button></a>
        </div>
        <div class="quiz2" id="q10">
            <p>MOVIES QUIZ</p>
            <img src="https://i.redd.it/6i3on4t5x5cb1.png" alt="">
            <a href="#instructions"><button class="btn2" id="startq9">START QUIZ</button></a>
        </div>
        <div class="quiz3" id="q11">
            <p>HORROR QUIZ</p>
            <img src="https://i.pinimg.com/564x/c6/81/14/c681140dee446a588d3db6cec58227ba.jpg" alt="">
            <a href="#instructions"><button class="btn2" id="startq10">START QUIZ</button></a>
        </div>
        <div class="quiz4" id="q12">
            <p>MUSIC QUIZ</p>
            <img src="https://static.vecteezy.com/system/resources/previews/011/532/347/original/music-doodle-illustration-free-vector.jpg" alt="">
            <a href="2.html"><button class="btn2">VIEW QUIZ TOPICS</button></a>
        </div>
    </div>
        <div class="content3">
            <div class="quiz5" id="q13">
                <p>TECHNOLOGY QUIZ</p>
                <img src="https://i.pinimg.com/originals/59/fd/c2/59fdc2e1b9b229116f30247e46f3ba7a.jpg" alt="">
                <a href="3.html"><button class="btn2">VIEW QUIZ TOPICS</button></a>
            </div>
            <div class="quiz6" id="q14">
                <p>CHEMISTRY QUIZ</p>
                <img src="https://m.media-amazon.com/images/I/71ZBLo85orL.AC_SL1500.jpg" alt="">
                <a href="#instructions"><button class="btn2" id="startq11">START QUIZ</button></a>
            </div>
            <div class="quiz7" id="q15">
                <p>SPACE QUIZ</p>
                <img src="https://img.freepik.com/premium-photo/4k-modern-colorful-3d-abstract-background-hd_669273-290.jpg" alt="">
                <a href="#instructions"><button class="btn2" id="startq12">START QUIZ</button></a>
            </div>
            <div class="quiz8" id="q16">
                <p>BIOLOGY QUIZ</p>
                <img src="https://th.bing.com/th/id/OIP.AnN_0g8jk_p2UR-1c_LUTgHaKe?&w=160&h=240&c=7&dpr=1.5&pid=ImgDet" alt="">
                <a href="#instructions"><button class="btn2" id="startq13">START QUIZ</button></a>
            </div>
        </div>
        <div class="content3">
            <div class="quiz5" id="q17">
                <p>JEE QUIZ</p>
                <img src="https://wallpaperbat.com/img/6903227-iit-jee-full-hd-wallpaper-study.jpg" alt="">
                <a href="4.html"><button class="btn2">VIEW QUIZ TOPICS</button></a>
            </div>
            <div class="quiz6" id="q18">
                <p>NEET QUIZ</p>
                <img src="https://yourviews.mindstick.com/Stories/04a66ca5-7789-47af-84d7-0817f95c6f8b/images/fd06bfe5-9d69-45e1-8e89-8996449e8c75.jpeg" alt="">
                <a href="5.html"><button class="btn2">VIEW QUIZ TOPICS</button></a>
            </div>
            <div class="quiz7" id="q19">
                <p>CAT EXAM QUIZ</p>
                <img src="https://img.freepik.com/premium-vector/school-elements-isolated-icon_24877-8650.jpg" alt="">
                <a href="6.html"><button class="btn2">VIEW QUIZ TOPICS</button></a>
            </div>
            <div class="quiz8" id="q20">
                <p>UPSC QUIZ</p>
                <img src="https://i.pinimg.com/originals/04/cc/15/04cc1571b513d58991882fbd5e6f91f6.jpg" alt="">
                <a href="7.html"><button class="btn2">VIEW QUIZ TOPICS</button></a>
            </div>
        </div>
    
    <section id="aboutus" class="bottom" style="margin:1rem;">
        <div class=""><h1>Know About Us</h1></div>
        <div class="details"></div>
        <div class="foot_middle_left">
            <h6><p> </p></h6>
        </div>
        <div class="foot_middle_right">
            <a href="" class="nav_link_contact"></a>
            <a href="" class="nav_link_contact"></a>
        </div>
        <div class="foot_middle_right">
            <div class="foot_mid_l_links" id="learn">
                <h4>LEARNING</h4>
                <p>In this website we make quiz games available for user so they can boost their knowledge. There are three levels of each quiz, each level is difficult than the one before thus increasing the difficulty making it more challenging and fun for the user. User can also time their quiz attempts and manage their profile. We make sure user have best expirience while playing these games. </p>
            </div>
            <div class="foot_mid_r_links">
            <h4>NAVIGATIONS</h4>
            <a href="index.html" class="nav_link_contact">Home</a><br>
            <a href="#q" class="nav_link_contact">Games</a><br>
            <a href="#profile" class="nav_link_contact">Profile</a><br>
            <a href="#HTML TAG" class="nav_link_contact">Contact Us</a>
            </div>
        </div>
        <div class="genre"><h4>GAMES</h4>
            <div class="nav_games">
                <div class="fun"><h6>FUN</h6>
                    <a href="#q5" class="gens">BOARD GAMES</a><br>
                    <a href="#q6" class="genm">MARVEL</a><br>
                    <a href="#q7" class="geng">GAMING</a><br>
                    <a href="#q8" class="genmu">CAR</a>
                </div>
                <div class="gk"><h6>GK</h6>
                    <a href="#q1" class="gent">MONUMENTS</a><br>
                    <a href="#q2" class="gensci">ANIMALS</a><br>
                    <a href="#q3" class="genh">HISTORY</a><br>
                    <a href="#q4" class="genc">COUNTRY</a>
                </div>
                <div class="entertainment"><h6>ENTERTAINMENT</h6>
                    <a href="1.html" class="gent">SPORTS</a><br>
                    <a href="#q10" class="gensci">MOVIES</a><br>
                    <a href="#q11" class="genh">HORROR</a><br>
                    <a href="2.html" class="genc">MUSIC</a>
                </div>
                <div class="science"><h6>SCIENCE</h6>
                    <a href="3.html" class="gent">TECHNOLOGY</a><br>
                    <a href="#q14" class="gensci">CHEMISTRY</a><br>
                    <a href="#q15" class="genh">SPACE</a><br>
                    <a href="#q16" class="genc">BIOLOGY</a>
                </div>
                <div class="science"><h6>EXAMS</h6>
                    <a href="4.html" class="gent">JEE</a><br>
                    <a href="5.html" class="gensci">NEET</a><br>
                    <a href="6.html" class="genh">CAT</a><br>
                    <a href="7.html" class="genc">UPSC</a>
                </div>
            </div>
        
        </div>
    </section>
</div>
    <div id="instructions">
        <div class="insheading">
        <button id="insexit">X</button>
        <h2 id="qinsheading">QUIZ INSTRUCTIONS:-</h2>
        </div>
        <div class="inslevel1">
        <h3>LEVEL-1</h3>
        <p>This level has 10 mcq type questions with four options.To unlock the next level you need atleast 4 correct answers.</p>
        <a href="quiz.php?quiz_type=Monuments&quiz_level=1"><button id="btmon">continue</button></a><a href="quiz.php?quiz_type=Animals&quiz_level=1"><button id="btan">continue</button></a><a href="quiz.php?quiz_type=History&quiz_level=1"><button id="bthis">continue</button></a><a href="quiz.php?quiz_type=Countries&quiz_level=1"><button id="btcon">continue</button></a><a href="quiz.php?quiz_type=BOARD GAMES&quiz_level=1"><button id="btlogo">continue</button></a><a href="quiz.php?quiz_type=Marvel&quiz_level=1"><button id="btmarvel">continue</button></a><a href="quiz.php?quiz_type=Gaming&quiz_level=1"><button id="btgame">continue</button></a><a href="quiz.php?quiz_type=Cars&quiz_level=1"><button id="btcar">continue</button></a><a href="quiz.php?quiz_type=Movies&quiz_level=1"><button id="btmovie">continue</button></a><a href="quiz.php?quiz_type=Horror&quiz_level=1"><button id="bthorror">continue</button></a><a href="quiz.php?quiz_type=Chemistry&quiz_level=1"><button id="btchem">continue</button></a><a href="quiz.php?quiz_type=Space&quiz_level=1"><button id="btspace">continue</button></a><a href="quiz.php?quiz_type=Biology&quiz_level=1"><button id="btbio">continue</button></a>
        </div>
        <div class="inslevel2">
        <h3>LEVEL-2</h3>
        <p>This level has 15 mcq type questions with four options.To attempt each question you will have a time limit of 15 seconds.To unlock the next level you need atleast 8 correct answers.</p>
        <a href="quiz.php?quiz_type=Monuments&quiz_level=2"><button id="btmon1">continue</button></a><a href="quiz.php?quiz_type=Animals&quiz_level=2"><button id="btan1">continue</button></a><a href="quiz.php?quiz_type=History&quiz_level=2"><button id="bthis1">continue</button></a><a href="quiz.php?quiz_type=Countries&quiz_level=2"><button id="btcon1">continue</button></a><a href="quiz.php?quiz_type=BOARD GAMES&quiz_level=2"><button id="btlogo1">continue</button></a><a href="quiz.php?quiz_type=Marvel&quiz_level=2"><button id="btmarvel1">continue</button></a><a href="quiz.php?quiz_type=Gaming&quiz_level=2"><button id="btgame1">continue</button></a><a href="quiz.php?quiz_type=Cars&quiz_level=2"><button id="btcar1">continue</button></a><a href="quiz.php?quiz_type=Movies&quiz_level=2"><button id="btmovie1">continue</button></a><a href="quiz.php?quiz_type=Horror&quiz_level=2"><button id="bthorror1">continue</button></a><a href="quiz.php?quiz_type=Chemistry&quiz_level=2"><button id="btchem1">continue</button></a><a href="quiz.php?quiz_type=Space&quiz_level=2"><button id="btspace1">continue</button></a><a href="quiz.php?quiz_type=Biology&quiz_level=2"><button id="btbio1">continue</button></a>
        </div>
        <div class="inslevel3">
        <h3>LEVEL-3</h3>
        <p>This level has 20 mcq type questions with four options.To attempt each question you will have a time limit of 10 seconds.</p>
        <a href="quiz.php?quiz_type=Monuments&quiz_level=3"><button id="btmon2">continue</button></a><a href="quiz.php?quiz_type=Animals&quiz_level=3"><button id="btan2">continue</button></a><a href="quiz.php?quiz_type=History&quiz_level=3"><button id="bthis2">continue</button></a><a href="quiz.php?quiz_type=Countries&quiz_level=3"><button id="btcon2">continue</button></a><a href="quiz.php?quiz_type=BOARD GAMES&quiz_level=3"><button id="btlogo2">continue</button></a><a href="quiz.php?quiz_type=Marvel&quiz_level=3"><button id="btmarvel2">continue</button></a><a href="quiz.php?quiz_type=Gaming&quiz_level=3"><button id="btgame2">continue</button></a><a href="quiz.php?quiz_type=Cars&quiz_level=3"><button id="btcar2">continue</button></a><a href="quiz.php?quiz_type=Movies&quiz_level=3"><button id="btmovie2">continue</button></a><a href="quiz.php?quiz_type=Horror&quiz_level=3"><button id="bthorror2">continue</button></a><a href="quiz.php?quiz_type=Chemistry&quiz_level=3"><button id="btchem2">continue</button></a><a href="quiz.php?quiz_type=Space&quiz_level=3"><button id="btspace2">continue</button></a><a href="quiz.php?quiz_type=Biology&quiz_level=3"><button id="btbio2">continue</button></a>
        </div>
    </div>
    
    <script>
        

        
        document.addEventListener('DOMContentLoaded', function() {
            const startbtn1 = document.getElementById("startq1");
            const startbtn2 = document.getElementById("startq2");
            const startbtn3 = document.getElementById("startq3");
            const startbtn4 = document.getElementById("startq4");
            const startbtn5 = document.getElementById("startq5");
            const startbtn6 = document.getElementById("startq6");
            const startbtn7 = document.getElementById("startq7");
            const startbtn8 = document.getElementById("startq8");
            const startbtn9 = document.getElementById("startq9");
            const startbtn10 = document.getElementById("startq10");
            const startbtn11 = document.getElementById("startq11");
            const startbtn12 = document.getElementById("startq12");
            const startbtn13 = document.getElementById("startq13");

            const ins = document.getElementById("instructions");
            const insexit = document.getElementById("insexit");
            const back = document.getElementById("main");
            const heading = document.getElementById("qinsheading");
            const monuments = document.getElementById("btmon");
            const animals = document.getElementById("btan");
            const historya = document.getElementById("bthis");
            const country = document.getElementById("btcon");
            const monuments1 = document.getElementById("btmon1");
            const animals1 = document.getElementById("btan1");
            const history1 = document.getElementById("bthis1");
            const country1 = document.getElementById("btcon1");
            const monuments2 = document.getElementById("btmon2");
            const animals2 = document.getElementById("btan2");
            const history2 = document.getElementById("bthis2");
            const country2 = document.getElementById("btcon2");
            const logo = document.getElementById("btlogo");
            const marvel = document.getElementById("btmarvel");
            const game = document.getElementById("btgame");
            const car = document.getElementById("btcar");
            const logo1 = document.getElementById("btlogo1");
            const marvel1 = document.getElementById("btmarvel1");
            const game1 = document.getElementById("btgame1");
            const car1 = document.getElementById("btcar1");
            const logo2 = document.getElementById("btlogo2");
            const marvel2 = document.getElementById("btmarvel2");
            const game2 = document.getElementById("btgame2");
            const car2 = document.getElementById("btcar2");
            const movies = document.getElementById("btmovie");
            const movies1 = document.getElementById("btmovie1");
            const movies2 = document.getElementById("btmovie2");
            const horror = document.getElementById("bthorror");
            const horror1 = document.getElementById("bthorror1");
            const horror2 = document.getElementById("bthorror2");
            const chem = document.getElementById("btchem");
            const chem1 = document.getElementById("btchem1");
            const chem2 = document.getElementById("btchem2");
            const space = document.getElementById("btspace");
            const space1 = document.getElementById("btspace1");
            const space2 = document.getElementById("btspace2");
            const biology = document.getElementById("btbio");
            const biology1 = document.getElementById("btbio1");
            const biology2 = document.getElementById("btbio2");

            
            const isLoggedIn = <?php echo isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true ? 'true' : 'false'; ?>;


            function hideAllCategoryButtons() {
                monuments.style.display = 'none';
                animals.style.display = 'none';
                historya.style.display = 'none';
                country.style.display = 'none';
                monuments1.style.display = 'none';
                animals1.style.display = 'none';
                history1.style.display = 'none';
                country1.style.display = 'none';
                monuments2.style.display = 'none';
                animals2.style.display = 'none';
                history2.style.display = 'none';
                country2.style.display = 'none';
                logo.style.display = 'none';
                marvel.style.display = 'none';
                game.style.display = 'none';
                car.style.display = 'none';
                logo1.style.display = 'none';
                marvel1.style.display = 'none';
                game1.style.display = 'none';
                car1.style.display = 'none';
                logo2.style.display = 'none';
                marvel2.style.display = 'none';
                game2.style.display = 'none';
                car2.style.display = 'none';
                movies.style.display = 'none';
                movies1.style.display = 'none';
                movies2.style.display = 'none';
                horror.style.display = 'none';
                horror1.style.display = 'none';
                horror2.style.display = 'none';
                chem.style.display = 'none';
                chem1.style.display = 'none';
                chem2.style.display = 'none';
                space.style.display = 'none';
                space1.style.display = 'none';
                space2.style.display = 'none';
                biology.style.display = 'none';
                biology1.style.display = 'none';
                biology2.style.display = 'none';
            }

            
            function handleQuizStart(quizHandler) {
                if (!isLoggedIn) {
                    
                    window.location.href = 'login.html';
                } else {
                    
                    quizHandler();
                }
            }

        
            function startQuiz1() {
                ins.classList.add('active');
                back.classList.add('active');
                hideAllCategoryButtons();
                monuments.style.display = 'block';
                monuments1.style.display = 'block';
                monuments2.style.display = 'block';
            }

            
            function startQuiz2() {
                ins.classList.add('active');
                back.classList.add('active');
                hideAllCategoryButtons();
                animals.style.display = 'block';
                animals1.style.display = 'block';
                animals2.style.display = 'block';
            }

            
            function startQuiz3() {
                ins.classList.add('active');
                back.classList.add('active');
                hideAllCategoryButtons();
                historya.style.display = 'block';
                history1.style.display = 'block';
                history2.style.display = 'block';
            }

            
            function startQuiz4() {
                ins.classList.add('active');
                back.classList.add('active');
                hideAllCategoryButtons();
                country.style.display = 'block';
                country1.style.display = 'block';
                country2.style.display = 'block';
            }

            
            function startQuiz5() {
                ins.classList.add('active');
                back.classList.add('active');
                hideAllCategoryButtons();
                logo.style.display = 'block';
                logo1.style.display = 'block';
                logo2.style.display = 'block';
            }

            
            function startQuiz6() {
                ins.classList.add('active');
                back.classList.add('active');
                hideAllCategoryButtons();
                marvel.style.display = 'block';
                marvel1.style.display = 'block';
                marvel2.style.display = 'block';
            }

            
            function startQuiz7() {
                ins.classList.add('active');
                back.classList.add('active');
                hideAllCategoryButtons();
                game.style.display = 'block';
                game1.style.display = 'block';
                game2.style.display = 'block';
            }

            
            function startQuiz8() {
                ins.classList.add('active');
                back.classList.add('active');
                hideAllCategoryButtons();
                car.style.display = 'block';
                car1.style.display = 'block';
                car2.style.display = 'block';
            }

            
            
            function startQuiz9() {
                ins.classList.add('active');
                back.classList.add('active');
                hideAllCategoryButtons();
                movies.style.display = 'block';
                movies1.style.display = 'block';
                movies2.style.display = 'block';
            }

            
            function startQuiz10() {
                ins.classList.add('active');
                back.classList.add('active');
                hideAllCategoryButtons();
                horror.style.display = 'block';
                horror1.style.display = 'block';
                horror2.style.display = 'block';
            }

        
            function startQuiz11() {
                ins.classList.add('active');
                back.classList.add('active');
                hideAllCategoryButtons();
                chem.style.display = 'block';
                chem1.style.display = 'block';
                chem2.style.display = 'block';
            }

            
            function startQuiz12() {
                ins.classList.add('active');
                back.classList.add('active');
                hideAllCategoryButtons();
                space.style.display = 'block';
                space1.style.display = 'block';
                space2.style.display = 'block';
            }

            
            function startQuiz13() {
                ins.classList.add('active');
                back.classList.add('active');
                hideAllCategoryButtons();
                biology.style.display = 'block';
                biology1.style.display = 'block';
                biology2.style.display = 'block';
            }

            
            startbtn1.onclick = function() { handleQuizStart(startQuiz1); };
            startbtn2.onclick = function() { handleQuizStart(startQuiz2); };
            startbtn3.onclick = function() { handleQuizStart(startQuiz3); };
            startbtn4.onclick = function() { handleQuizStart(startQuiz4); };
            startbtn5.onclick = function() { handleQuizStart(startQuiz5); };
            startbtn6.onclick = function() { handleQuizStart(startQuiz6); };
            startbtn7.onclick = function() { handleQuizStart(startQuiz7); };
            startbtn8.onclick = function() { handleQuizStart(startQuiz8); };
            startbtn9.onclick = function() { handleQuizStart(startQuiz9); };
            startbtn10.onclick = function() { handleQuizStart(startQuiz10); };
            startbtn11.onclick = function() { handleQuizStart(startQuiz11); };
            startbtn12.onclick = function() { handleQuizStart(startQuiz12); };
            startbtn13.onclick = function() { handleQuizStart(startQuiz13); };

            insexit.onclick = () => {
                ins.classList.remove('active');
                back.classList.remove('active');
                hideAllCategoryButtons();
            }

            
            const allLevelButtons = document.querySelectorAll("#instructions button");
            allLevelButtons.forEach(button => {
                
                if (button.id !== "insexit") {
                    const originalOnclick = button.onclick;
                    button.onclick = function(e) {
                        if (!isLoggedIn) {
                            e.preventDefault();
                            window.location.href = 'login.html';
                            return false;
                        }
                        return true;
                    };
                }
            });

            
            const viewTopicButtons = document.querySelectorAll(".btn2:not([id^='startq'])");
            viewTopicButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!isLoggedIn) {
                        e.preventDefault();
                        window.location.href = 'login.html';
                        return false;
                    }
                    return true;
                });
            });
        });
    </script>
</body>
<footer class="text-center py-4 mt-5" style="background-color: #f8f9fa;">
        <p>© 2025 CRACKQUIZ. All rights reserved.</p>
    </footer>
</html>