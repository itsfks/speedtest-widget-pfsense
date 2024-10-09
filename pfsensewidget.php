<?php

require_once("guiconfig.inc");

if ($_REQUEST['ajax']) {
    $apiUrl = "http://api_server:5000/speedtest/latest";
    $results = file_get_contents($apiUrl);
    $results = json_decode($results, true);
    
    if ($results !== null) {
        $config['widgets']['speedtest_result'] = json_encode($results);
        write_config("Save speedtest results");
        echo json_encode($results);
    } else {
        echo json_encode(null);
    }
} else {
    $results = isset($config['widgets']['speedtest_result']) ? json_decode($config['widgets']['speedtest_result'], true) : null;

    if ($results === null) {
        $results = [];
    }
    
    function format_timestamp($timestamp) {
        return date("d/m/Y - H:i:s", strtotime($timestamp));
    }
?>
<table class="table">
    <tr>
        <td><h4>Ping <i class="fa fa-exchange"></i></h4></td>
        <td><h4>Download <i class="fa fa-download"></i></h4></td>
        <td><h4>Upload <i class="fa fa-upload"></i></h4></td>
    </tr>
    <tr>
        <td><h4 id="speedtest-ping"><?php echo isset($results['ping_latency']) ? round($results['ping_latency']) . " <small>ms</small>" : "N/A"; ?></h4></td>
        <td><h4 id="speedtest-download"><?php echo isset($results['download_bandwidth']) ? number_format($results['download_bandwidth'] / 125000, 2) . " <small>Mbps</small>" : "N/A"; ?></h4></td>
        <td><h4 id="speedtest-upload"><?php echo isset($results['upload_bandwidth']) ? number_format($results['upload_bandwidth'] / 125000, 2) . " <small>Mbps</small>" : "N/A"; ?></h4></td>
    </tr>
    <tr>
        <td>Servidor</td>
        <td colspan="2" id="speedtest-server"><?php echo isset($results['server_name']) ? htmlspecialchars($results['server_name']) : "N/A"; ?></td>
    </tr>
    <tr>
        <td>Localização</td>
        <td colspan="2" id="speedtest-location"><?php echo isset($results['location']) ? htmlspecialchars($results['location']) : "N/A"; ?></td>
    </tr>
    <tr>
        <td>País</td>
        <td colspan="2" id="speedtest-country"><?php echo isset($results['country']) ? htmlspecialchars($results['country']) : "N/A"; ?></td>
    </tr>
    <tr>
        <td colspan="3" id="speedtest-ts" style="font-size: 0.8em;"><?php echo isset($results['timestamp']) ? "Último atualização: " . format_timestamp($results['timestamp']) : ""; ?></td>
    </tr>
</table>
<a id="updspeed" href="#" class="fa fa-refresh" style="display: none;"></a>
<script type="text/javascript">
function update_result(results) {
    if (results != null) {
        $("#speedtest-ts").html("Última atualização: " + format_timestamp(results.timestamp));
        $("#speedtest-ping").html(Math.round(results.ping_latency) + "<small> ms</small>");
        $("#speedtest-download").html((results.download_bandwidth / 125000).toFixed(2) + "<small> Mbps</small>");
        $("#speedtest-upload").html((results.upload_bandwidth / 125000).toFixed(2) + "<small> Mbps</small>");
        $("#speedtest-location").html(results.location);
        $("#speedtest-country").html(results.country);
        $("#speedtest-server").html(results.server_name);
    } else {
        $("#speedtest-ts").html("Speedtest failed");
        $("#speedtest-ping").html("N/A");
        $("#speedtest-download").html("N/A");
        $("#speedtest-upload").html("N/A");
        $("#speedtest-location").html("N/A");
        $("#speedtest-country").html("N/A");
        $("#speedtest-server").html("N/A");
    }
}

function format_timestamp(timestamp) {
    const date = new Date(timestamp);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const seconds = String(date.getSeconds()).padStart(2, '0');
    return `${day}/${month}/${year} - ${hours}:${minutes}:${seconds}`;
}

function update_speedtest() {
    $('#updspeed').off("click").blur().addClass("fa-spin").click(function() {
        $('#updspeed').blur();
        return false;
    });
    $.ajax({
        type: 'POST',
        url: "/widgets/widgets/speedtest.widget.php",
        dataType: 'json',
        data: {
            ajax: "ajax"
        },
        success: function(data) {
            update_result(data);
        },
        error: function() {
            update_result(null);
        },
        complete: function() {
            $('#updspeed').off("click").removeClass("fa-spin").click(function() {
                update_speedtest();
                return false;
            });
        }
    });
}

events.push(function() {
    var target = $("#updspeed").closest(".panel").find(".widget-heading-icon");
    $("#updspeed").prependTo(target).show();
    $('#updspeed').click(function() {
        update_speedtest();
        return false;
    });
    update_result(<?php echo ($results === null ? "null" : json_encode($results)); ?>);
    
    update_speedtest();
});
</script>
<?php } ?>
