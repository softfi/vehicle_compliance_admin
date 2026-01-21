<?php include("header.php"); ?>

<!-- Page Body Start-->
<div class="page-body-wrapper">
  <?php include("mainsidebar.php"); ?>
  <div class="page-body">
    <div class="container-fluid">
      <div class="page-title">
        <div class="row">
          <div class="col-sm-6 p-0">
            <h3>Default Dashboard</h3>
          </div>
          <div class="col-sm-6 p-0">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="index.html">
                  <svg class="stroke-icon">
                    <use href="https://admin.pixelstrap.net/dunzo/<?php echo base_url(); ?>/assets/admin/svg/icon-sprite.svg#stroke-home"></use>
                  </svg></a></li>
              <li class="breadcrumb-item">Dashboard</li>
              <li class="breadcrumb-item active">Default</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <!-- Container-fluid starts-->
    <div class="container-fluid default-dashboard">
      <style>
        #map {
          height: 500px;
          width: 100%;
          margin-top: 20px;
        }
      </style>

      <h2>Select Vehicle Number:</h2>
      <select id="vehicleSelect" class="form-control" style="width: 300px;">
        <option value="">-- Select Vehicle --</option>
      </select>

      <div id="map"></div>

      <?php
      // 🚀 Fetch data from API using PHP to avoid CORS issues
      $api_url = "https://api.wheelseye.com/currentLoc?accessToken=05fbd3f9-c522-4725-9607-318c9c527cf4";
      $response = file_get_contents($api_url);
      $vehicleData = json_decode($response, true);
      ?>

      <script>
        let map;
        let marker;
        let vehicleList = <?php echo json_encode($vehicleData['data']['list']); ?>;

        function initMap() {
          map = new google.maps.Map(document.getElementById('map'), {
            center: { lat: 21.75, lng: 84.0 },
            zoom: 8
          });

          populateDropdown();
        }

        function populateDropdown() {
          const select = document.getElementById('vehicleSelect');

          vehicleList.forEach((vehicle, index) => {
            const opt = document.createElement('option');
            opt.value = index;
            opt.text = vehicle.vehicleNumber;
            select.appendChild(opt);
          });

          select.addEventListener('change', function () {
            const selected = vehicleList[this.value];
            if (selected) {
              showLocationOnMap(selected);
            }
          });
        }

        function showLocationOnMap(vehicle) {
          const latLng = {
            lat: parseFloat(vehicle.latitude),
            lng: parseFloat(vehicle.longitude)
          };

          map.setCenter(latLng);
          map.setZoom(15);

          if (marker) {
            marker.setPosition(latLng);
          } else {
            marker = new google.maps.Marker({
              position: latLng,
              map: map
            });
          }

          const infoWindow = new google.maps.InfoWindow({
            content: `<strong>${vehicle.vehicleNumber}</strong><br>${vehicle.location}`
          });

          infoWindow.open(map, marker);
        }
      </script>

      <!-- ✅ Google Maps with your actual API key -->
      <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDUV4Bnczw13DzefH1MSQ2VjmtKX7xIB_o&callback=initMap" async defer></script>
    </div>
    <!-- Container-fluid Ends-->
  </div>

  <!-- Footer -->
  <?php include("footer.php"); ?>
</div>
