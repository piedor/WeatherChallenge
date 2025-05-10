const hour = new Date().getHours();
if (hour < 6 || hour > 18) {
    document.body.style.background = "linear-gradient(to bottom, #2c3e50, #34495e)";
} else if (hour < 12) {
    document.body.style.background = "linear-gradient(to bottom, #87CEFA, #ffffff)";
} else {
    document.body.style.background = "linear-gradient(to bottom, #FFA500, #ffffff)";
}