<style>
    .enter {
        animation: barEnter .25s ease-out forwards;
    }

    .leave {
        animation: barExit .25s ease-out forwards;
    }
    .notification .text {
        display: inline-block;
        position: relative;
        width: calc(100vw - 104px);
        font-size: 16px;
        padding: 15px 15px;
        opacity: 1;
        animation: textEnter .25s .25s ease-out forwards;
    }
    .notification {
        position: absolute;
        z-index: 9999;
        width: 100%;
        top: 0;
        left: 0;
        color: #fff;
        height: 0px;
        overflow: hidden;
        font-weight: 300;
    }

    .success {
        background-color: #77B576;
    }
    .danger {
        background: #CC5C5C;
    }
    @keyframes barEnter {
        from {
            opacity: 0;
            height: 0px;
        }
        to {
            opacity: 1;
            height: 50px;
        }
    }

    @keyframes barExit {
        from{
            opacity: 1;
            height: 50px;
            margin-top: 0px;
        }
        to {
            opacity: 0;
            height: 0px;
        }
    }
    .wait {
        position: absolute;
        display: none;
        align-items: center;
        justify-content: center;
        background: #FFF;
        z-index: 9999;
        width: 100%;
        height: 109%;

    }
    .wait.show {
        display: flex !important;
    }
</style>
<div style=" position: relative; " >
    <div class="wait">
        <div class="loader"></div>
    </div>
    <div class="notification success ">

        <div class="text">
            <?= __("Location saved successfully")   ?>
        </div>
    </div>

    <div id="mapid" style="height: 350px;"></div>
    <div class="mapFooter " style=" text-align: center; padding: 10px; margin-bottom: 20px; ">
        <div class="col-sm-12 col-12 col-lg-12 col-md-12 form-group" style="">
            <button onclick="saveLocation()" type="submit" class="btn btn-primary form_control btn-save disabled" style="margin-bottom: 0;padding: 10px;" > <?= __("Save location")   ?> </button>
        </div>
    </div>


</div>

<script>
    var lat,lng;
    function saveLocation() {
        $.get('/Institution/Institutions/updateInstitutionCoordinate', {longitude: lng, latitude: lat});
        $("div.success").addClass("enter");
        setTimeout(function(){
            $("div.success").removeClass("enter");
        }, 3999);
        setTimeout(function(){
            $("div.success").addClass("leave");
            setTimeout(function(){$("div.success").removeClass("leave");},1000);
        }, 4000);
    }
    $( document ).ready(function() {

        if (!window.mymap) {

            window.mymap = L.map('mapid',{
                maxZoom: 18,
                minZoom: 7,
                maxBounds: [
                    //south west
                    [43.600, 69.0],
                    //north east
                    [39.10, 80.6]
                ],
            }).setView([<?= $attr['mapPosition']['lat'] . ', ' . $attr['mapPosition']['lng'] ?>], <?= $attr['mapConfig']['zoom'] ?>);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',



            }).addTo(mymap);
            var LeafIcon = L.Icon.extend({
                options: {
                    iconSize: [38, 55],
                    iconAnchor: [19, 54]
                }
            });
            var greenIcon = new LeafIcon({
                iconUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png'
            })
            var marker = L.marker([<?= $attr['mapPosition']['lat'] . ', ' . $attr['mapPosition']['lng'] ?>], {icon: greenIcon}).addTo(mymap);
            marker.dragging.enable();
            marker.on('dragend', function (e) {
                lat = marker.getLatLng().lat;
                lng = marker.getLatLng().lng;
                $('.mapFooter .btn-save').removeClass('disabled')


            });
        }



    });
</script>
