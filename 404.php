<?php
$server = "51.83.255.41";
$bd_name = "nuitdelinfo";
$user = "debian";
$password = "nuitdelinfo";
try {
    $bdd=new PDO('mysql:host='.$server.'; dbname='.$bd_name.'; charset=utf8', $user, $password);
} catch (PDOException $e) {
    try {
        $bdd=new PDO('sqlite:data/data');
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}
$sth = $bdd->prepare("SELECT * FROM scoreboard ORDER BY score LIMIT 3");
$sth->execute();
$tops = $sth->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">    
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>404 : Not Found</title>
    <style>
        canvas {
            border:1px solid #d3d3d3;
            background-color: #f1f1f1;
        }
    </style>
</head>
<body onload="startGame()">
    <script>
        
        var joueur;
        var obj = [];        
        var score;
        var gameWidth = 720;
        var gameHeight = 405;
        var gravitySpeed = 1;
        var nbObj = 1;
        var status = 0;
        var sfxApl;
        var sfxTaxe;
        let spriteApl;
        var spriteTaxe;
        var spriteJoueur;
        
        function startGame() {            
            joueur = new component(32, 32, "red", 360, gameHeight - 96, "joueur");
            score = new component("30px", "Consolas", "black", Math.round(gameWidth / 2), gameHeight - 24, "text");
            sfxApl = new Audio('sfx1.ogg');
            sfxTaxe = new Audio('sfx2.ogg');
            spriteApl = new Image();
            spriteApl.src = "apl.png";
            spriteTaxe = new Image();
            spriteTaxe.src = "taxe.png";
            spriteJoueur = new Image();
            spriteJoueur.src = "joueur.png";
            gameArea.start();
        }

        function random(min, max) {
            return Math.floor(Math.random() * (max - min + 1) + min);
        }

        var gameArea = {
            canvas : document.createElement("canvas"),
            start : function() {
                this.canvas.width = gameWidth;
                this.canvas.height = gameHeight;
                this.context = this.canvas.getContext("2d");
                document.body.insertBefore(this.canvas, document.body.childNodes[0]);
                this.frame = 0;
                this.interval = setInterval(updateGameArea, 20);
                },
            clear : function() {
                this.context.clearRect(0, 0, this.canvas.width, this.canvas.height);
            }
        }

        function component(width, height, color, x, y, type) { 
            this.type = type;
            this.width = width;
            this.height = height;
            this.speedX = 0;
            this.speedY = 0;
            this.x = x;
            this.y = y;
            this.gravity = 0;
            if (this.type != "joueur" && this.type != "text") {
                this.gravitySpeed = gravitySpeed;
                if (this.type == "apl") {
                    this.score = 500;
                } else {
                    this.score = 0.5;
                }
            } else {
                this.gravitySpeed = 0;
                this.score = 0;
                if (this.type == "joueur") {
                    this.curFrame = 0;
                    this.frameCount = 8;   
                    this.srcY = 3;         
                    }
                }            
            this.update = function() {
                ctx = gameArea.context;
                if (this.type == "text") {
                    ctx.font = this.width + " " + this.height;
                    ctx.fillStyle = color;
                    ctx.fillText(this.text, this.x, this.y);
                } else if (this.type == "apl") {
                    ctx.drawImage(spriteApl, this.x, this.y, this.width, this.height);
                } else if (this.type == "taxe") {
                    ctx.drawImage(spriteTaxe, this.x, this.y, this.width, this.height);
                } else if (this.type == "joueur") {
                    this.curFrame = ++this.curFrame % this.frameCount;
                    this.srcX = this.curFrame * this.width + 3;
                    ctx.drawImage(spriteJoueur,this.srcX,this.srcY,this.width,this.height,this.x,this.y,this.width,this.height);
                } else {
                    ctx.fillStyle = color;
                    ctx.fillRect(this.x, this.y, this.width, this.height);
                }
            }
            this.newPos = function() {
                this.gravitySpeed += this.gravity;
                this.x += this.speedX;
                this.y += this.speedY + this.gravitySpeed;
                if (this.type == "joueur") {
                    this.checkWallsjoueur();
                }
            }
            this.checkWallsjoueur = function() {
                var bottom = gameArea.canvas.height - this.height;
                var right = gameArea.canvas.width - this.width;   
                if (this.x < 0) {
                    this.x = 0;
                    this.speedX = 0;
                }             
                if (this.x > right) {
                    this.x = right;
                    this.speedX = 0;
                }                
            }
            this.checkWallsObj = function() {
                if (status == 1) {
                    return;
                }
                var bottom = gameArea.canvas.height - this.height;                  
                return this.y > bottom;
            }
            this.collision = function(obj) {
                if (this.y <= (obj.y + obj.height) && obj.y <= (this.y + this.height)) {
                    if (this.x <= (obj.x + obj.width) && obj.x <= (this.x + this.width)) {
                        return true;
                    }
                }
                return false;
            }            
        }

        function updateGameArea() {
            var xMax, objX, type;
            for (var i = 0; i < obj.length; i++) {
                if (joueur.collision(obj[i])) {
                    if (obj[i].type == "apl") {
                        score.score += obj[i].score;
                        sfxApl.play();
                        obj.splice(i, 1);
                    } else {
                        score.score -= score.score * obj[i].score;
                        obj.splice(i, 1);
                    }checkWallsObj
                }
                if (obj[i].checkWallsObj() && obj[i].type == "apl") {
                   gameOver();
                   return;
                }
            } 
            gameArea.clear();
            gameArea.frame++;
            if (gameArea.frame == 1 || everyinterval(150 - (10 * nbObj))) {
                gravitySpeed += 0.1;
                if ((Math.round(gravitySpeed,1) % 1.5) == 0) {
                    nbObj++;
                }
                xMax = gameArea.canvas.width;
                type = random(1,10);
                objX = random(0, xMax-16);
                for (let i = 0; i < nbObj; i++) {                                                     
                    if (type != 1) { // 1 chance sur 10 que ce l'objet crée soit une taxe
                        obj.push(new component(16, 16, "green", objX, 0, "apl"));
                    } else {
                        obj.push(new component(16, 16, "red", objX, 0, "taxe"));
                    }
                }
                
            }
            for (var i = 0; i < obj.length; i++) {
                obj[i].newPos();
                obj[i].update();                    
            }                
            var singulier = score.score == 1 || score.score == 0;
            if (singulier) {
                score.text = score.score + "€ récupéré"
            } else {
                score.text = score.score + "€ récupérés"
            }                
            score.update();
            joueur.newPos();
            joueur.update();            
        }
        function everyinterval(n) {
            return (gameArea.frame / n) % 1 == 0;
        }

        document.addEventListener('keydown', function(event) {
            if(event.keyCode == 37) {
                if (joueur.speedX > 0) {
                    joueur.speedX = 0;
                } else if (joueur.speedX < -10) {
                    joueur.speedX = -10
                } else {
                    joueur.speedX -= 1;
                }
            }
            else if(event.keyCode == 39) {
                if (joueur.speedX < 0) {
                    joueur.speedX = 0;
                } else if (joueur.speedX > 10) {
                    joueur.speedX = 10;
                } else joueur.speedX += 1;
            }
        });

        function gameOver() {      
            status = 1;      
            gameArea.canvas = null;
            var result = document.createElement('div');
            result.id = "result";
            result.innerHTML = "<p> Score final : " + score.score + " </p>";
            document.body.appendChild(result);
            var scoreboard = document.createElement('div');
            scoreboard.id = "scoreboard";
            document.body.appendChild(scoreboard);
            var scoreboardTable = document.createElement('table');
            scoreboard.appendChild(scoreboardTable);
            scoreboardTable.style.border = '1px solid black';
            var tops = <?php echo json_encode($tops); ?>;
            tops.forEach(top => {
                var row = scoreboardTable.insertRow(0);
                var cell1 = row.insertCell(0);
                var cell2 = row.insertCell(1);
                cell1.innerHTML = top[0];
                cell2.innerHTML = top[1];
            });          
        }

    </script>
    <br>
    <p>Erreur 404 : La page à laquelle vous essayez d'accéder n'existe pas</p>
</body>
</html>