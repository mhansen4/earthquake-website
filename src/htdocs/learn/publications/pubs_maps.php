<?php
      //  Author: Tiffany Kalin
      //  Contact: Lisa Wald
      if  (!isset($TEMPLATE))  {
      $TITLE  =  'Publications - Maps';
      $NAVIGATION  = true;
      include  'template.inc.php';
      }

echo
'<figure class="right">
  <img src="images/maps.jpg" alt="Map" />
</figure>';

//get database
include_once '/etc/puppet/EHPServer.class.php';
$pdo = EHPServer::getDatabase('earthquake');

//show selected category - maps
$statement = $pdo->prepare("
    SELECT *
    from productsCategory
    WHERE id=:catID
    ORDER BY category");

try {
  // use bound parameter names
  $statement->execute(array(
    ':catID' => 1
  ));

  //for each row..
  while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
    $categoryID = $row["id"];
    $category = $row["category"];
    $check=$pdo->prepare("
        SELECT *
        from products
        WHERE categoryID=:categoryID"
      );

    try {
      $check->execute(array(
        ':categoryID' => $categoryID
      ));

      //check to see if there are products associated with each category
      //if not - don't show the category
      $num_productst = $check->rowCount();
      if(!empty($num_productst)) {
        if(!isset($i)) {
          $i = 0;
        }
        $i++;

        //get sub category
        $getSubCAT = $pdo->prepare("
            SELECT *
            from productsSubCategory
            WHERE categoryID=:categoryID
            ORDER BY subCategory");

        try {
          $getSubCAT->execute(array(
            ':categoryID' => $categoryID
          ));

          while ($row = $getSubCAT->fetch(PDO::FETCH_ASSOC)) {
            $subCategoryID = $row["id"];
            $subCategory = $row["subCategory"];

            //check to see if there are products associated with each sub category
            //if not - don't show the sub category
            $checkSub=$pdo->prepare("
                SELECT *
                from products
                WHERE categoryID=:categoryID AND subCategoryID=:subCategoryID"
              );

            try {
              $checkSub->execute(array(
                ':categoryID' => $categoryID,
                ':subCategoryID' => $subCategoryID
              ));

              $num_sub = $checkSub->rowCount();
              if(!empty($num_sub)) {
                if($subCategory != "No Sub Category"){
                  echo "<h2>$subCategory</h2>\n<ul>\n";
                }else{
                  echo "<ul>";
                }

                //get products/links
                $getLINK = $pdo->prepare("
                    SELECT *
                    from products
                    WHERE categoryID=:categoryID AND subCategoryID=:subCategoryID
                    ORDER BY id DESC");

                try {
                  $getLINK->execute(array(
                    ':categoryID' => $categoryID,
                    ':subCategoryID' => $subCategoryID
                  ));

                  while ($row = $getLINK->fetch(PDO::FETCH_ASSOC)) {
                    $link = urldecode($row["linkURL"]);
                    $link = (htmlspecialchars($link));
                    $text = htmlspecialchars(stripslashes($row["linkText"]));
                    $description = stripslashes($row["description"]);

                    echo "
                        <li><a href=$link>$text</a>";

                    if(!empty($description)){
                      echo "<br />$description";
                    }
                    echo "</li>\n";
                  }
                  echo "</ul>";

                  $getLINK->closeCursor();
                }

                //$getLINK catch statement
                catch (PDOException $e) {
                  // don't output this on prod...
                  trigger_error($e->getMessage());
                }

                //free prepared statement
                $getLINK = null;
              }

              //close cursor
              $checkSub->closeCursor();
            }

            //$checkSub catch statement
            catch (PDOException $e) {
              // don't output this on prod...
              trigger_error($e->getMessage());
            }

            //free prepared statement
            $checkSub = null;
          }

          //close cursor
          $getSubCAT->closeCursor();
        }

        //$getSubCAT catch statement
        catch (PDOException $e) {
          // don't output this on prod...
          trigger_error($e->getMessage());
       }

       //free prepared statement
        $getSubCAT = null;
      }

      //close cursor
      $check->closeCursor();
    }

    //$check catch statement
    catch (PDOException $e) {
      // don't output this on prod...
      trigger_error($e->getMessage());
    }

    //free prepared statement
    $check = null;
  }

  // must close cursor before calling execute again
  $statement->closeCursor();
}

//$statement catch statement
catch (PDOException $e) {
  // don't output this on prod...
  trigger_error($e->getMessage());
}

// free prepared statement
$statement = null;

// close database connection
$pdo = null;
?>
