<?php 
	
    session_start();
    require 'vendor/autoload.php';

    date_default_timezone_set('UTC');

    use Aws\DynamoDb\DynamoDbClient;
    use Aws\DynamoDb\Marshaler;
    use Aws\DynamoDb\Exception\DynamoDbException;

    $client = new DynamoDbClient([
        'region'  => 'us-east-1',
        'version' => 'latest',
        'credentials' => array(
            'key' => 'AKIAQU5YQXS6OS3GQUVC',
            'secret'  => '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',
        )
        
]);

if (array_key_exists('email', $_POST) && array_key_exists('password', $_POST) ) {
        
    $email = $_POST['email']; 

    $password = trim($_POST['password']);
    
    $Flag = FALSE;
    
    $marshaler = new Marshaler();
    
    $tableName = 'logindata';
    
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

        $dbpassword = $result['Item']['password'];

        $dbpassword1 = $dbpassword['S'];

    } catch (DynamoDbException $e) {

    echo "Unable to get item:\n";

    echo $e->getMessage() . "\n";

}

$Flag = FALSE;

if ($dbpassword1 == $password)
{   
    $Flag = TRUE; 
}

if ($Flag == TRUE)
{ 
    $Flag = false;


    $_SESSION['email'] = $email;

    echo '<script language=javascript>window.location.href="/main.php?email='.$_SESSION['email'].'"</script>';

    
}
else
{
    echo "email or password is invalid";
}

}
?>

<html>  
<head>  
    <title>Login system</title>  
    
    <link rel = "stylesheet" type = "text/css" href = "style.css">   
</head>  

<body align="center">  
    <div id = "frm">  
        <h1>Login Form</h1>  
        <form action="#" method="post">
            <p>  
                <label> Email: </label>  
                <input type = "text" id ="email" name  = "email" required />  
            </p>  
            <p>  
                <label> Password: </label>  
                <input type = "password" id ="password" name  = "password" required />  
            </p>  
            <p>     
                <input type =  "submit" id = "btn" value = "Login" /> 
    </p>

    <p>
    <label> Don't have an account ? </label>     
    <a href='/register.php' target="_self">Register</a>
            </p> 
        </form>  
    </div>  
    
</body>     
</html>  