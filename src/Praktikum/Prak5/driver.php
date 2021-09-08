<?php declare(strict_types=1);
// UTF-8 marker äöüÄÖÜß€
/**
 * Driver class that represents the 'driver' page
 *
 * PHP Version 7.4
 *
 * @file     Driver.php
 * @package  Prak2
 * @author   Bernhard Kreling, <bernhard.kreling@h-da.de>
 * @author   Ralf Hahn, <ralf.hahn@h-da.de>
 * @version  3.0
 */

require_once './base/Page.php';
require_once './model/OrderItem.php';
require_once './model/Order.php';

/**
 * This is a template for top level classes, which represent
 * a complete web page and which are called directly by the user.
 * Usually there will only be a single instance of such a class.
 * The name of the template is supposed
 * to be replaced by the name of the specific HTML page e.g. baker.
 * The order of methods might correspond to the order of thinking
 * during implementation.
 * @author   Bernhard Kreling, <bernhard.kreling@h-da.de>
 * @author   Ralf Hahn, <ralf.hahn@h-da.de>
 */
class Driver extends Page
{
    // to do: declare reference variables for members 
    // representing substructures/blocks


    /**
     * Instantiates members (to be defined above).
     * Calls the constructor of the parent i.e. page class.
     * So, the database connection is established.
     * @throws Exception
     */
    protected function __construct()
    {
        parent::__construct();
        // to do: instantiate members representing substructures/blocks
    }

    /**
     * Cleans up whatever is needed.
     * Calls the destructor of the parent i.e. page class.
     * So, the database connection is closed.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Fetch all data that is necessary for later output.
     * Data is returned in an array e.g. as associative array.
	 * @return array An array containing the requested data. 
	 * This may be a normal array, an empty array or an associative array.
     */
    protected function getViewData(): array
    {
        $orders = array();
        //only ordering, which has no ordered article with status <= 2 will be returned
        $query  = "SELECT article.name,article.picture,article.price,ordered_article.*,ordering.address FROM ordered_article JOIN article,ordering WHERE ordering.ordering_id = ordered_article.ordering_id  and  ordered_article.article_id = article.article_id AND ordering.ordering_id not IN (SELECT ordering_id FROM ordered_article WHERE ordered_article.status < 2 or ordered_article.status = 4)";

        $records = $this->_database->query($query);
        if (!$records) {
            throw new Exception("Query failed: ". $this->_database->error);
        }
        //same as order item class but with extra address
        while ($record = $records->fetch_assoc()) {
            $order = new OrderItemToDelivery(
                htmlspecialchars($record["name"]),
                htmlspecialchars($record["ordered_article_id"]),
                htmlspecialchars($record["ordering_id"]),
                htmlspecialchars($record["status"]),
                htmlspecialchars($record["picture"]),
                htmlspecialchars($record["price"]),
                htmlspecialchars($record["address"])
            );
            $orders[$order->itemId] = $order;
        }
        $records->free();

        // we group all order with same ordering id together
        $orderGroupByOrderingID = array();
        foreach($orders as $id => $item) {
            if (isset($orderGroupByOrderingID[$item->orderId])) {
                // update price
                $orderGroupByOrderingID[$item->orderId]->price += $item->price;
                // is there any other way to pushback element to array ?
                array_push($orderGroupByOrderingID[$item->orderId]->ordered_article_id,$item->itemId);
                array_push($orderGroupByOrderingID[$item->orderId]->ordered_article_name,$item->name);
            }else {
                $newItem = new Order(
                    $item->orderId,
                    $item->status,
                    $item->price,
                    $item->address,
                );
                $orderGroupByOrderingID[$item->orderId] = $newItem;
                //push back itemId and name . no need right now
                array_push($orderGroupByOrderingID[$item->orderId]->ordered_article_id,$item->itemId);
                array_push($orderGroupByOrderingID[$item->orderId]->ordered_article_name,$item->name);
            }
        }
        return $orderGroupByOrderingID;
    }

    /**
     * First the required data is fetched and then the HTML is
     * assembled for output. i.e. the header is generated, the content
     * of the page ("view") is inserted and -if available- the content of
     * all views contained is generated.
     * Finally, the footer is added.
	 * @return void
     */
    protected function generateView():void
    {
		$data = $this->getViewData();
        $this->generatePageHeader("Fahrer");

        echo "<main>";
        if (isset($_GET["status"]) && $_GET["status"] == "update_success") {
            echo "Erfolgreich aktualisiert";
        }

        echo <<< EOT
            <h1 class="text-center">Auslieferungsübersicht</h1>
            <div class="row">
                <div class="col">
                    <h2>Alle auszuliefernden Pizzen </h2>             
                        <form action="driver.php" accept-charset="UTF-8" method="post" class="auto-submit-form">
        EOT;
        if (sizeof($data) == 0) echo "<h3>There are no order to do !!</h3>";
        foreach ($data as $order){
            $this->printOrderedArticle($order);
        }

        echo <<< EOT
                        </form>
                </div>
            </div>
        </main>

        <script src="scripts/radio-ui.js"></script>

        EOT;

        $this->generatePageFooter();
    }
    /**
     * Print article Status for each pizza
     *
     * @param OrderItem an order item
     */
    public function printOrderedArticle($order): void
    {
        echo <<< EOT
        <div class="pizza">
            <section>
                <p class="text-bold">Auszuliefern an: </p>
                <p>{$order->address}</p>
            </section>
            <div class="pizza-meta">
                <p>Bestellung #{$order->orderId} <p>
        EOT;
        foreach ($order->ordered_article_name as $name)
            echo "<p>Pizza - $name</p>";
        echo <<< EOT
                <p>{$order->price} €</p>
                <div class="div-input">
        EOT;

        $this->printButton($order->orderId, $order->status, 'gebacken', 2);
        $this->printButton($order->orderId, $order->status, 'unterwegs', 3);
        $this->printButton($order->orderId, $order->status, 'geliefert', 4);

        echo <<< EOT
                </div>
            </div>
        </div>
        EOT;
    }

    /**
     * 
     */
    private function printButton($id, $status, $statusName, $value): void
    {
        $attribute = $status == $value ? "checked" : "";

        echo <<< EOT
        <input type="radio" name='statuses[$id]' id="radio-$id-$value" value=$value $attribute class="auto-submit-btn"/>
        <label for="radio-$id-$value">$statusName</label>
        EOT;
    }

    /**
     * Processes the data that comes via GET or POST.
     * If this page is supposed to do something with submitted
     * data do it here.
	 * @return void
     */
    protected function processReceivedData():void
    {
        parent::processReceivedData();

        if (isset($_POST['statuses'])){   
            $statuses = $_POST['statuses'];
            foreach($statuses as $id => $status){
                $escapedId = $this->_database ->real_escape_string((string)$id);
                $escapedStatus = $this->_database ->real_escape_string((string)$status);
                $sqlQuery = "UPDATE ordered_article SET status = $escapedStatus WHERE ordering_id = $escapedId";

                $recordset = $this->_database->query($sqlQuery);
                if (!$recordset) {
                    throw new Exception("Query failed!" . $this->_database->error);
                }
            }

            header('Location: driver.php?status=update_success');
            die();
            return;
        }
    }

    /**
     * This main-function has the only purpose to create an instance
     * of the class and to get all the things going.
     * I.e. the operations of the class are called to produce
     * the output of the HTML-file.
     * The name "main" is no keyword for php. It is just used to
     * indicate that function as the central starting point.
     * To make it simpler this is a static function. That is you can simply
     * call it without first creating an instance of the class.
	 * @return void
     */
    public static function main():void
    {
        try {
            $page = new Driver();
            $page->processReceivedData();
            $page->generateView();
        } catch (Exception $e) {
            //header("Content-type: text/plain; charset=UTF-8");
            header("Content-type: text/html; charset=UTF-8");
            echo $e->getMessage();
        }
    }
}

// This call is starting the creation of the page. 
// That is input is processed and output is created.
Driver::main();

// Zend standard does not like closing php-tag!
// PHP doesn't require the closing tag (it is assumed when the file ends). 
// Not specifying the closing ? >  helps to prevent accidents 
// like additional whitespace which will cause session 
// initialization to fail ("headers already sent"). 
//? >