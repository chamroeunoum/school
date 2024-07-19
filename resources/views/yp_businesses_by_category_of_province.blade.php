<?php dd( $tableHeader ); ?>
<table>
  <tr>
    @foreach( $tableHeader AS $headIndex => $headItem )
    <th>{{ $headItem }}</th>
    @endforeach
  </tr>
    @foreach( $tableBody AS $bodyIndex => $bodyRow )
    <tr >
      @foreach( $bodyRow AS $bodyCellIndex => $bodyRowCell )
      <td>{{ $bodyRowCell }}</td>
      @endforeach  
    </tr>
    @endforeach
  
</table>