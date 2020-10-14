window.onload = function() {
  var onceUponATimeReadMores = document.getElementsByClassName("once-upon-a-time-read-more");
  var i;
  for (i = 0; i < onceUponATimeReadMores.length; i++) {
    onceUponATimeReadMores[i].addEventListener("click", function() {
      this.classList.toggle("once-upon-a-time-show-details");
      var onceUponATimeDescription = this.nextElementSibling;
      if (onceUponATimeDescription.style.display === "block") {
        onceUponATimeDescription.style.display = "none";
        this.innerHTML = document.getElementById("once-upon-a-time-read-more-text").innerHTML;
      } else {
        onceUponATimeDescription.style.display = "block";
        this.innerHTML = document.getElementById("once-upon-a-time-read-less-text").innerHTML;
      }
    });
  }
}
