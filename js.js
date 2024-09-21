function imgCycleCall(){
    setInterval(imgCycle, 3000)
}
let timer = 5
function imgCycle(){
    // let newText= "test text 2"
    var imgpath = ["https://www.awsfzoo.com/media/capy1.png", "https://cdn.pixabay.com/photo/2016/10/11/16/14/capybara-1732020_1280.jpg","https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSKLcMW-TJelg6Rs1Y_x5kT_-nGGX9F8W-b0A&s","https://t3.ftcdn.net/jpg/04/91/78/04/360_F_491780452_Ctg5Twa1RJwJgWPBZ4ImOib4uTmMr8SL.jpg", "https://ichef.bbci.co.uk/childrens-responsive-ichef-live/976/childrens-binary-store/r/1x/cbeebies/SAAGW-Capybara-3.jpg"]
    // document.getElementById("text").innerHTML=newText
    // document.getElementById("img1").src=imgpath[0]

    if (timer <= 5){
        timer = timer - 1
    }
    if (4 == timer){
        document.getElementById("img1").src=imgpath[0]

    }
    if (3 == timer){
        document.getElementById("img1").src=imgpath[1]

    }
    if (2 == timer){
        document.getElementById("img1").src=imgpath[2]
    
    }
    if (1 == timer){
        document.getElementById("img1").src=imgpath[3]

    }
    if (0 == timer){
        document.getElementById("img1").src=imgpath[4]
        timer=5
    }
    
    // if (0 == timer){
    //     timer=6
    
    // }
    console.log(timer)
}