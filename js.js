function imgCycleCall(){
    setInterval(imgCycle, 3000)
}
let timer = 4
let visible=true
let opacity=1
let intCount=0

function imgCycle(){
    // let newText= "test text 2"
    var imgpath = ["https://www.awsfzoo.com/media/capy1.png", "https://cdn.pixabay.com/photo/2016/10/11/16/14/capybara-1732020_1280.jpg","https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSKLcMW-TJelg6Rs1Y_x5kT_-nGGX9F8W-b0A&s","https://t3.ftcdn.net/jpg/04/91/78/04/360_F_491780452_Ctg5Twa1RJwJgWPBZ4ImOib4uTmMr8SL.jpg", "https://ichef.bbci.co.uk/childrens-responsive-ichef-live/976/childrens-binary-store/r/1x/cbeebies/SAAGW-Capybara-3.jpg"]
    var setText = ["<font size='7' color='black'><b> <i> <center> A FASTER WAY FORWARD <font size='4'> </br>WELCOME TO GENERATION ORANGE </center>", "<font size='8' color='black'><b> <i> <center> POWER YOUR ARROW <font size='4'> </br>WELCOME TO GENERATION ORANGE </center>"]
    // document.getElementById("text").innerHTML=newText
    // document.getElementById("img1").src=imgpath[0]

    if (timer <= 5){
        timer = timer - 1
    }
    if (4 == timer){
        document.getElementById("img1").style.opacity=1
        document.getElementById("img1").src=imgpath[0]
        document.getElementById("testtext").innerHTML=setText[0]
        setTimeout(function(){console.log("Wait")},1000)

        opacity=1
        setTimeout(fadetest,1000)
        

    }
    if (3 == timer){
        document.getElementById("img1").style.opacity=1

        document.getElementById("img1").src=imgpath[1]

        document.getElementById("testtext").innerHTML=setText[0]
        setTimeout(function(){console.log("Wait")},1000)
        opacity=1

        setTimeout(fadetest,1000)


    }
    if (2 == timer){
        document.getElementById("img1").style.opacity=1

        document.getElementById("img1").src=imgpath[2]

        document.getElementById("testtext").innerHTML=setText[0]
        setTimeout(function(){console.log("Wait")},1000)
        opacity=1

        setTimeout(fadetest,1000)

    
    }
    if (1 == timer){
        document.getElementById("img1").style.opacity=1

        document.getElementById("img1").src=imgpath[3]

        document.getElementById("testtext").innerHTML=setText[1]
        setTimeout(function(){console.log("Wait")},1000)
        opacity=1

        setTimeout(fadetest,1000)


    }
    if (0 == timer){
        document.getElementById("img1").style.opacity=1

        document.getElementById("img1").src=imgpath[4]
        document.getElementById("testtext").innerHTML=setText[1]
        setTimeout(function(){console.log("Wait")},1000)
        opacity=1

        setTimeout(fadetest,1000)


        timer=5
    }
    
    // if (0 == timer){
    //     timer=6
    
    // }
    console.log(timer)
}
function fadetest(){
console.log(opacity)
var img = document.getElementById("img1")
    var i=setInterval(function(){
        intCount++
            opacity-=0.1
            img.style.opacity=opacity
            console.log(opacity)
        if (intCount===10){
            clearInterval(i)
        }
    },100)


}

