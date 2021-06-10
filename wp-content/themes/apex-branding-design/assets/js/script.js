const crsom_video_container = document.querySelector("#home-case-study");
const video_btn = document.querySelector("#show-video");
const video = document.querySelector("#angie-video");

video_btn.addEventListener("click", () => {
    crsom_video_container.classList.add("show");
    video.src="https://www.youtube-nocookie.com/embed/H5-HaRzq4Wc?modestbranding=1";
})

crsom_video_container.addEventListener("click", () => {
    crsom_video_container.classList.remove("show");
    video.src=" ";
 

})