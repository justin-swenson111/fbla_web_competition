let timer = 0
function imgCall(){
    setInterval(imgCycle, 1000)
}
function imgCycle(){
    var imgpath = ["./media/coding.png", "./media/automotivechilds.png","./media/Energyheader.png","./media/Vet-Sci-new.png"]

    if (timer<=2){
        timer-=1
    }
    if (timer==-1){
        document.getElementById("img1").src=imgpath[0]
        document.getElementById("test").innerHTML="1"
        timer=3
    }
    if (timer == 0){
        document.getElementById("img1").src=imgpath[1]
        document.getElementById("test").innerHTML="2"

    }
    if (timer == 1){
        document.getElementById("img1").src=imgpath[2]
        document.getElementById("test").innerHTML="3"
    }
    if (timer == 2){
        document.getElementById("img1").src=imgpath[3]
        document.getElementById("test").innerHTML="4"
    }

}