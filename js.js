
let timer = 6
                function start(){
               
                if (timer <= 6){
                    timer = timer - 1
                }
                if (5 == timer){
                    document.getElementById("text").innerHTML = "<font size='7' color='white'><b> <i> <center> A FASTER WAY FORWARD <font size='4'> </br>WELCOME TO GENERATION ORANGE </center>"
                    document.getElementById("img1").innerHTML = "<p><img src='https://www.west-mec.edu/cms/lib/AZ50010839/Centricity/Domain/4/Powesports-new-1.png' width='500' height='500'></p>"   
                }
                
                if (4 == timer){
                    document.getElementById("text").innerHTML = "<font size='7' color='white'><b> <i> <center> A FASTER WAY FORWARD <font size='4'> </br>WELCOME TO GENERATION ORANGE </center>"
                    document.getElementById("img1").innerHTML = "<p><img src='https://www.west-mec.edu/cms/lib/AZ50010839/Centricity/Domain/4/Vet-Sci-new.png' width='500' height='500'></p>"   
                }
                if (3 == timer){
                    document.getElementById("text").innerHTML = "<font size='8' color='white'><b> <i> <center> POWER YOUR ARROW <font size='4'> </br>WELCOME TO GENERATION ORANGE </center>"
                    document.getElementById("img1").innerHTML = "<p><img src='https://th-thumbnailer.cdn-si-edu.com/bgmkh2ypz03IkiRR50I-UMaqUQc=/1000x750/filters:no_upscale():focal(1061x707:1062x708)/https://tf-cmsv2-smithsonianmag-media.s3.amazonaws.com/filer_public/55/95/55958815-3a8a-4032-ac7a-ff8c8ec8898a/gettyimages-1067956982.jpg' width='500' height='500'></p>"   
                }
                if (2 == timer){
                    document.getElementById("text").innerHTML = "<font size='7' color='white'><b> <i> <center> A FASTER WAY FORWARD <font size='4'> </br>WELCOME TO MEC-WEST </center>"
                    document.getElementById("img1").innerHTML = "<p><img src='https://i.natgeofe.com/n/548467d8-c5f1-4551-9f58-6817a8d2c45e/NationalGeographic_2572187_square.jpg' width='500' height='500'></p>"   
                }
                if (1 == timer){
                    document.getElementById("text").innerHTML = "<font size='8' color='white'><b> <i> <center> POWER YOUR ARROW <font size='4'> </br>WELCOME TO GENERATION ORANGE </center>"
                    document.getElementById("img1").innerHTML = "<p><img src='https://g.petango.com/photos/466/1c76253e-6051-4690-942b-94e9055bcf0c.jpg' width='500' height='500'></p>"   
                }


                if (timer <= 0)
                    timer = 6
                
          console.log(timer)
          setTimeout(start, 1500)
            }
    function imgCycle(){
        var imgpath = ["https://www.west-mec.edu/cms/lib/AZ50010839/Centricity/Domain/4/Powesports-new-1.png", "https://www.west-mec.edu/cms/lib/AZ50010839/Centricity/Domain/4/Vet-Sci-new.png"]
        document.createElement('img').src = "https://www.west-mec.edu/cms/lib/AZ50010839/Centricity/Domain/4/Powesports-new-1.png"
        document.getElementById('text')=="This is a test"
        setTimeout(imgCycle, 1500)
        if (timer <= 6){
            timer = timer - 1
        }
        if (5 == timer){
        }
    }