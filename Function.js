async function fetcher(cityName) {
    let data;
    if (navigator.onLine) {
        const response = await fetch(`connection.php?q=${cityName}`);
        data = await response.json();
        localStorage.setItem(cityName, JSON.stringify(data));
    }
    
    else{
        data = JSON.parse(localStorage.getItem(cityName));
    }
    var city = document.getElementById("city");
        city.innerHTML = data[0].city;

        var condition = document.getElementById("condition");
        condition.innerHTML = data[0].weather_condition;

        var temperature = document.getElementById("celcius");
        temperature.innerHTML = data[0].temperature + '℃';

        var humidity = document.getElementById("Humidity");
        humidity.innerHTML = 'Humidity: ' + data[0].humidity + '%';

        var wind_speed = document.getElementById("wind");
        wind_speed.innerHTML = 'Wind Speed: ' + data[0].wind_speed + " m/s";

        var pressure = document.getElementById("pressure");
        pressure.innerHTML = 'Pressure: ' + data[0].pressure + " mm of Hg";

        var direction = document.getElementById("Wind_Direction");
        direction.innerHTML = 'Wind Direction: ' + data[0].wind_direction + '°';

        var icon = document.getElementById("icon");
        var iconcode = data[0].icon;
        console.log(iconcode);
        icon.innerHTML = `<img src="https://openweathermap.org/img/wn/${iconcode}@2x.png" alt="">`;
        document.getElementById("Day").innerHTML = data[0].fetched_at;}


fetcher("Belfast");

const cityInput = document.querySelector(".search_input");
const searchButton = document.getElementById("search");
searchButton.addEventListener("click", (e) => {
    e.preventDefault();
    const cityName = cityInput.value.trim();
    fetcher(cityName);
});
