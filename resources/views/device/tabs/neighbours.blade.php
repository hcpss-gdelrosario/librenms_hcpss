@extends('device.index')

@section('tab')
    <x-option-bar name="Neighbours" :options="$data['selections']" :selected="$data['selection']"></x-option-bar>
@if($data['selection'] == 'list')
    <table class="table table-hover table-condensed" id="neighbour-table">
        <thead>
            <tr>
                <th>Local Port</th>
                <th>Remote Device</th>
                <th>Remote Port</th>
                <th>Protocol</th>
            </tr>
        </thead>
        <tbody>
@foreach($data['links'] as $link)
@if($link['rdev_url'])
            <tr>
                <td>{!! $link['local_url'] !!}<br />{{ $link['local_portname'] }}</td>
                <td>{!! $link['rdev_url'] !!}<br />{{ $link['rdev_info'] }}</td>
                <td>{!! $link['rport_url'] !!}<br />{{ $link['rport_name'] }}</td>
                <td>{{ $link['protocol'] }}</td>
            </tr>
@else
            <tr>
                <td>{!! $link['local_url'] !!}<br />{{ $link['local_portname'] }}</td>
                <td>{{ $link['rdev_name'] }}<br />{{ $link['rdev_info'] }}</td>
                <td>{{ $link['rport_name'] }}</td>
                <td>{{ $link['protocol'] }}</td>
            </tr>
@endif
@endforeach
        </tbody>
    </table>
@elseif($data['selection'] == 'map')
    <div id="netmap"></div>

@push('scripts')
<script>
var network_nodes = new vis.DataSet({queue: {delay: 100}});
var network_edges = new vis.DataSet({queue: {delay: 100}});

$.post( '{{ route('maps.getdevicelinks') }}', {device: {{$data['device_id']}}, link_types: @json($data['link_types'])})
    .done(function( data ) {
        var devices = [];
        $.each(data, function( link_id, link ) {
            var this_edge = link['style'];
            this_edge['from'] = link['ldev'];
            this_edge['to'] = link['rdev'];
            this_edge['label'] = link['ifnames'];

            network_edges.add(this_edge);
            devices[link['ldev']] = true;
            devices[link['rdev']] = true;
        });

        $.post( '{{ route('maps.getdevices') }}', {devices: Object.keys(devices), url_type: 'links'})
            .done(function( data ) {
                $.each(data, function( dev_id, dev ) {
                    var this_dev = {id: dev_id, label: dev["sname"], title: dev["url"], shape: "box"};
                    if (dev["style"]) {
                        // Merge the style if it has been defined
                        this_dev = Object.assign(dev["style"], this_dev);
                    }
                    network_nodes.add(this_dev);
                });

                network_nodes.flush();
            });
        network_edges.flush();
    });


var height = $(window).height() - 100;
$('#netmap').height(height + 'px');

// create a network
var container = document.getElementById('netmap');
var options = {!! $data['visoptions'] !!};
var data = {
    nodes: network_nodes,
    edges: network_edges,
    stabilize: true
};

var network = new vis.Network(container, data, options);
network.on('click', function (properties) {
    if (properties.nodes > 0) {
       window.location.href = "{{ @url('device') }}/device="+properties.nodes+"/tab=neighbours/selection=map/"
    }
});
</script>
@endpush
@endif
@endsection

@section('javascript')
@if($data['selection'] == 'map')
    <script src="{{ url('js/vis.min.js') }}"></script>
@endif
@endsection
