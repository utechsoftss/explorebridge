<?php
/**
 * Template Name: Design My Trip
 */

get_header();
?>

<div class="container mt-5">
  <form id="tripSearchForm" method="GET" action="/MyTrip/trip-results" style="max-width: 600px; margin: auto;">
    <h2 style="text-align: center;">Design My Trip</h2>

    <!-- Trip Type -->
    <div style="margin-bottom: 15px;">
      <label><strong>Trip Type:</strong></label>
      <select name="trip_type" id="tripType" required>
        <option value="">-- Select Trip Type --</option>
        <option value="group">Group Tour</option>
        <option value="private">Private Tour</option>
      </select>
    </div>

    <!-- Start Date -->
    <div style="margin-bottom: 15px;">
      <label><strong>Start Date:</strong></label>
      <!--<select name="start_date" id="startDate" disabled></select>-->
       <input type="date" name="start_date" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;" id="startDate" disabled>
    </div>

    <!-- Destination -->
    <div style="margin-bottom: 15px;">
      <label><strong>Destination:</strong></label>
      <select name="destination" id="destination" disabled></select>
    </div>

    <!-- Departure City -->
    <div style="margin-bottom: 15px;">
      <label><strong>Departure City:</strong></label>
      <select name="departure_city" id="departureCity" disabled></select>
    </div>

    <!-- Vehicle Type -->
    <div style="margin-bottom: 15px;">
      <label><strong>Vehicle Type:</strong></label>
      <select name="vehicle_type" id="vehicleType" disabled></select>
    </div>

    <!-- Adults -->
    <div style="margin-bottom: 15px;">
      <label><strong>Adults:</strong></label>
      <input type="number" name="adults" min="1" value="1" required>
    </div>

    <!-- Kids -->
    <div style="margin-bottom: 20px;">
      <label><strong>Kids:</strong></label>
      <input type="number" name="kids" min="0" value="0">
    </div>

    <button type="submit" style="width: 100%; background-color: #cc6b37; color: white; padding: 12px;">Search</button>
  </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const tripType = document.getElementById("tripType");
  const startDate = document.getElementById("startDate");
  const destination = document.getElementById("destination");
  const departureCity = document.getElementById("departureCity");
  const vehicleType = document.getElementById("vehicleType");

  tripType.addEventListener("change", function () {
    const selectedType = this.value;

    [startDate, destination, departureCity, vehicleType].forEach(el => {
      el.innerHTML = '<option value="">Loading...</option>';
      el.disabled = true;
    });

    fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=load_trip_options&trip_type=' + selectedType)
      .then(res => res.json())
      .then(data => {
        startDate.innerHTML = '<option value="">-- Select Start Date --</option>';
        data.dates.forEach(date => {
          startDate.innerHTML += `<option value="${date}">${date}</option>`;
        });
        startDate.disabled = false;

        destination.innerHTML = '<option value="">-- Select Destination --</option>';
        data.destinations.forEach(dest => {
          destination.innerHTML += `<option value="${dest.slug}">${dest.name}</option>`;
        });
        destination.disabled = false;

        departureCity.innerHTML = '<option value="">-- Select City --</option>';
        data.departure_cities.forEach(city => {
          departureCity.innerHTML += `<option value="${city}">${city}</option>`;
        });
        departureCity.disabled = false;

        vehicleType.innerHTML = '<option value="">-- Select Vehicle --</option>';
        data.vehicles.forEach(v => {
          vehicleType.innerHTML += `<option value="${v}">${v}</option>`;
        });
        vehicleType.disabled = false;
      });
  });
});
</script>

<?php get_footer(); ?>
