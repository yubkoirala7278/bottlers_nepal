{{-- resources/views/warehouse/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Warehouse Locations')

@section('content')
<div class="table-container">
    <h2 style="padding: 1rem;">Warehouse Locations Overview</h2>
    <table>
        <thead>
            <tr>
                <th>Location</th>
                <th>Level</th>
                <th>Height</th>
                <th>Max Depth</th>
                <th>Current Fill</th>
                <th>Available</th>
                <th>Fill Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($locations as $location)
            @php
                $fillRate = ($location->current_fill / $location->max_depth) * 100;
                $color = $fillRate >= 80 ? '#f44336' : ($fillRate >= 50 ? '#ff9800' : '#4caf50');
            @endphp
            <tr>
                <td><strong>{{ $location->location_code }}</strong></td>
                <td>{{ $location->level }}</td>
                <td>{{ $location->height }}</td>
                <td>{{ $location->max_depth }}</td>
                <td>{{ $location->current_fill }}</td>
                <td>{{ $location->available_space }}</td>
                <td>
                    <div style="background: #e0e0e0; border-radius: 10px; overflow: hidden;">
                        <div style="background: {{ $color }}; width: {{ $fillRate }}%; height: 20px; transition: width 0.3s;"></div>
                    </div>
                    <span style="font-size: 0.75rem;">{{ number_format($fillRate, 1) }}%</span>
                 </td>
             </tr>
            @endforeach
        </tbody>
     </table>
</div>
@endsection