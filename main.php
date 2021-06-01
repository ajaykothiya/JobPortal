<?php

require 'vendor/autoload.php';

date_default_timezone_set('UTC');

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Aws\DynamoDb\Exception\DynamoDbException;

?>

<?php

$email = $_GET['email'];

$marshaler = new Marshaler();

$tableName = 'logindata';

$client = new DynamoDbClient([
    'region'  => 'us-east-1',
    'version' => 'latest',
    'credentials' => array(
        'key' => 'AKIAQU5YQXS6OS3GQUVC',
        'secret'  => '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',
    )
]);

$key = $marshaler->marshalJson('
{
    
    "email": "' . $email . '"

}

');

    $params = [

        'TableName' => $tableName,

        'Key' => $key

    ];

try {

    $result = $client->getItem($params);

    $username = $result['Item']['username'];

    $username1 = $username['S'];

} catch (DynamoDbException $e) {

echo "Unable to get item:\n";

echo $e->getMessage() . "\n";

}


echo "<p align='right'>".$username1."</p>";
echo "<p align='right'> <a href='/logout.php'>"."Logout"."</a> </p>";

$tableName1='music';

$tableName2='subscription';

//$params = [

  //  'TableName' => $tableName2

//];

$eav = $marshaler->marshalJson('

{

":email": "'.$email.'"

}

');

$params = [

'TableName' => $tableName2,

'ProjectionExpression' => '#yr, title, artist, img_url',

'FilterExpression' => ' email = :email',

'ExpressionAttributeNames'=> [ '#yr' => 'year' ],

'ExpressionAttributeValues'=> $eav

];

try{

    $result1 = $client->scan($params);

    echo "<p align='center'> <font color=black  size='6pt'><h3>Subscription Display Area </h3> </font> </p>";

    if ($result1['Items'] == null) {

        echo "No subscribed music";

    } else {
    
        echo "<table align='center' border='1' style='width:800px'>
        <tr>
            <th>Title</th>
            <th>Artist</th>
            <th>Year</th>
            <th>Image</th>
            <th>Action</th>
        </tr>";


        foreach($result1['Items'] as $i)

        {

        $entity = $marshaler->unmarshalItem($i);
        
        echo "<tr>";
        echo "<td>" . $entity['title'] . "</td>";
        echo "<td>" . $entity['artist'] . "</td>";
        echo "<td>" . $entity['year'] . "</td>";
        echo '<td> <img src ="https://s3752868-bucket.s3.amazonaws.com/'.$entity['img_url'].'" width="120" height="120" /></td>';
        $key = $entity['title'];
        echo "<td> <a href='/remove.php?key=".urlencode($key)."&email=".urlencode($email)."'>Remove</a></td>";
        echo "</tr>";
    
    }

    echo "</table>";

    }
} catch (DynamoDbException $e) {

    echo "Unable to get item:\n";
    
    echo $e->getMessage() . "\n";
    
    }


?>

<html>  
<head>  

    <link rel = "stylesheet" type = "text/css" href = "style.css"> 
</head> 


<body align="center">  
    
<div id = "frm1">  
        <h3>Query Area</h3>  
        <form action="#" method="post">
        <p>  
                <label> Title: </label>  
                <input type = "text" name='title' id='title' size='30'>
            </p>  
             <p>  
                <label> Artist: </label>  
                <input type = "text" name='artist' id='artist' size='30'>
            </p>  
            <p>  
                <p>
                <label> Year: </label> 

                <input type = "text" name='year' id='year' size='30'>
            </p>

            <p>     
                <input type =  "Submit" id = "btn" name = "query" value = "Query"/>   
             </p>
        </form>  
</div>  
    
</body>  
</html>

<?php

$tableName1 = 'music';

if(isset($_POST['query'])) {

    $title = $_POST['title'];
    
    $artist = $_POST['artist'];
    
    $year = $_POST['year'];

    $eav2 = "{";

        $filterExpression="";


        if($title != null)

        {

            $eav2= $eav2. '":title": "' . $title . '"';

            $filterExpression = 'title = :title';

            if($artist != null || $year!= null)

            {

                $eav2 = $eav2 . ',';

                $filterExpression = $filterExpression . ' and ';

            }
	

        }

        if($artist != null)

        {

            $eav2= $eav2. '":artist": "' . $artist . '"';

            $filterExpression = $filterExpression. 'artist = :artist';

            if($year!= null)

            {

                $eav2 = $eav2 . ',';

                $filterExpression = $filterExpression . ' and ';


            }
	


        }
        
        if($year != null)
    {
	    $eav2= $eav2. '":year": "' . $year . '"';
	    $filterExpression = $filterExpression. '#yr = :year';
    }

    $eav2 = $eav2. '}';

    $eav = $marshaler->marshalJson($eav2);

    if($title !=null || $artist !=null || $year!=null){
    
        $params = [
        'TableName' => $tableName1,
        
        'FilterExpression' => $filterExpression ,
        
        'ProjectionExpression' => '#yr, title, artist, img_url',
        
        'ExpressionAttributeNames'=> [ '#yr' => 'year' ],
        
        'ExpressionAttributeValues'=> $eav

    ];

}

else{

    $params = [
        
        'TableName' => $tableName1
    
    ];

    }


    try{
    

        $result = $client->scan($params);

        if($result["Items"] != null){

            echo "<table align='center' border='1' style='width:800px'>
        <tr>
            <th>Title</th>
            <th>Artist</th>
            <th>Year</th>
            <th>Image</th>
            <th>Action</th>
        </tr>";

	foreach ($result['Items'] as $i) {
        
        $entity = $marshaler->unmarshalItem($i);

        echo "<tr>";
        echo "<td>" . $entity['title'] . "</td>";
        echo "<td>" . $entity['artist'] . "</td>";
        echo "<td>" . $entity['year'] . "</td>";
        echo '<td> <img src ="https://s3752868-bucket.s3.amazonaws.com/'.basename($entity['img_url']).'" width="120" height="120" /></td>';
        $key = $entity['title'];
        $artist = $entity['artist'];
        
        $flag=false;

        foreach($result1['Items'] as $i)
        {

            $entity1 = $marshaler->unmarshalItem($i);

            if($entity1['title'] == $entity['title']) {
                $flag = TRUE;
                break;

            }

        }
        $entity = $marshaler->unmarshalItem($i);
        if($flag==false) {
            echo "<td> <a href='/subscribe.php?key=".urlencode($key)."&artist=".urlencode($artist)."&email=".urlencode($email)."'>Subscribe</a></td>";
        } else {
            echo "<td> </td>";
        }
        
        echo "</tr>";
                        
        }
        
        echo "</table>";

  	}
	else {

       
		echo "No result is retrieved. Please query again.";
		
	}
     
}
catch (DynamoDbException $e) 
    {
        echo "Unable to add musical:\n";
        echo $e->getMessage() . "\n";
        
    }
}
?>

