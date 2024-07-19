<table>
@foreach( $data AS $index => $val )
<tr>
  <td>{{ ( $index + 1 ) }}</td>
  <td>{{ $val->objective }}</td>
  <td>{{ $val->document_year }}</td>
</tr>
@endforeach
</table>