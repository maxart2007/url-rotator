<?php
    include 'Template.php';
    
    if (empty($_GET['test_id'])) {
        echo "Test is not defined.";
    } else {
        session_start();
        $test_id = filter_var($_GET['test_id'], FILTER_SANITIZE_STRING);
        
        $db = new SQLite3('urlrotator.db');
        
        $statement = $db->prepare('SELECT * FROM urls WHERE test_id = :test_id AND user_session IS :current_session;');
        $statement->bindValue(':test_id', $test_id);
        $statement->bindValue(':current_session', $_COOKIE[session_name()]);
        $result = $statement->execute();
        $test = $result->fetchArray();
        
        if (empty($test)) {
            $statement = $db->prepare('SELECT * FROM urls WHERE test_id = :test_id AND user_session IS NULL;');
            $statement->bindValue(':test_id', $test_id);
    
            $result = $statement->execute();
            
            $test = $result->fetchArray();
        }
        
        
        if (!empty($test)) {
            $statement->close();
            // var_dump($one_item);
            $statement = $db->prepare('UPDATE urls SET user_session = :user_session WHERE id = :url_id;');
            $statement->bindValue(':url_id', $test['id']);
            $statement->bindValue(':user_session', $_COOKIE[session_name()]);
            $result = $statement->execute();
            $statement->close();
            
            if (empty($test['login'])) {
                header("Location: " . $test['url']);
            } else {
                Template::view('index.html', [
                    'url' => $test['url'],
                    'login' => $test['login'],
                    'password' => $test['password']
                ]);
                // echo "URL: " . $one_item['url'] . "<br>";
                // echo "Login: " . $one_item['login'] . "<br>";
                // echo "Password: " . $one_item['password'] . "<br>";
            }
        } else {
            echo 'nothing found or left';
            $statement->close();
        }
    }
?>