<?php 
  function readPageWithCurl($url){
    $data = null;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch,CURLOPT_URL, $url );

    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13");
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
  }
?>
<table>
<?php 

  $categoriesCounter = 0 ;
  for( $i = 1 ; $i <=12 ; $i++ ) {
    libxml_use_internal_errors(true);
    $doc = new DOMDocument;
    // We don't want to bother with white spaces
    $doc->preserveWhiteSpace = false;
    
    // function read html page with curl
    $data = readPageWithCurl( "https://ypkhmer.com/category/subcategory/all?page=".$i );

    $doc->loadHTML($data);

    $xpath = new DOMXPath($doc);

    // // We start from the root element
    // $query = "//div[@class='col-lg-6 col-md-8 col-12 p-md-0 p-3 listing']";
    $query = "//div[@class='row pb-2']/div[@class='col-md-6 col-12']/a[@class='section-b']";
    $dls = $xpath->query($query);
    foreach($dls as $dlsIndex => $dl ) {
      $categoriesCounter++;
      if( $dl === null ) continue ;
      // dd( $dl->attributes[0]->value );
    ?>
      <tr>
        <td>{{ $categoriesCounter }}</td>
        <td>{{ str_replace([" ","\n","•"],[""],$dl->childNodes[1]->textContent ) }}</td>
        <td>{{ $dl->attributes[0]->value }}</td>
      </tr>
     <?php
    }
  }
?>
</table>