<?php
include_once("Connection.php");
include_once("Slim/Slim.php");
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();
$app->response()->header("Content-Type", "application/json;charset=utf-8");

$app->get("/tasks/", function () {
	$query = Connection::getInstance()->query("SELECT type,content FROM tasks WHERE done=0 ORDER BY sort_order");
	if($query->rowCount() > 0) {
		$tasks = $query->fetchAll(PDO::FETCH_ASSOC);
		$json = array("tasks"=>$tasks);
		echo json_encode($json);
	}
	else {
		$json = array("error"=>"WOW! You have nothing else to do. Enjoy the rest of your day!");
		echo json_encode($json);
	}
});

$app->get("/task/:id/", function ($uuid) {
	$query = Connection::getInstance()->query("SELECT * FROM tasks WHERE uuid=".$uuid." AND done=0");
	if($query->rowCount() == 1) {
		$task = $query->fetchAll(PDO::FETCH_ASSOC);
		$json = array("task"=>$task);
		echo json_encode($json);
	}
	else {
		$json = array("error"=>"This task not exist!");
		echo json_encode($json);
	}
});

$app->post("/task/", function () use ($app) {
	$request = \Slim\Slim::getInstance()->request();
	$data = json_decode($request->getBody());
	if(empty($data->content) || !isset($data->content)) {
		$json = array("error"=>"Bad move! Try removing the task instead of deleting its content.");
		echo json_encode($json);
	}
	else {
		if($data->type != "shopping" && $data->type != "work") {
			$json = array("error"=>"The task type you provided is not supported. You can only use shopping or work.");
			echo json_encode($json);
		}
		else {
			$check_order = Connection::getInstance()->query("SELECT MAX(sort_order) as lastId FROM tasks WHERE done=0");
			if($check_order->rowCount() > 0) {
				$task = $check_order->fetch(PDO::FETCH_ASSOC);
				$sort_order = $task["lastId"] + 1;
			}
			else {
				$sort_order = 0;
			}

			$query = Connection::getInstance()->prepare("INSERT INTO tasks (type,content,sort_order) VALUES (:type, :content, :sort_order)");
			$query->bindValue(":type", $data->type);
			$query->bindValue(":content", $data->content);
			$query->bindValue(":sort_order", $sort_order);
			$query->execute();
			$json = array("OK"=>"The task is created!");
			echo json_encode($json);
		}
	}
});

$app->delete("/task/:id/", function ($uuid) {
	$query = Connection::getInstance()->query("SELECT * FROM tasks WHERE uuid=".$uuid." AND done=0");
	if($query->rowCount() == 1) {
		Connection::getInstance()->query("UPDATE tasks SET done=1 WHERE uuid=".$uuid."");
		$json = array("OK"=>"The task was deleted!");
		echo json_encode($json);
	}
	else {
		$json = array("error"=>"Good news! The task you were trying to delete didn't even exist!");
		echo json_encode($json);
	}
});

$app->put("/task/", function () use ($app) {
	$request = \Slim\Slim::getInstance()->request();
	$data = json_decode($request->getBody());

	if(isset($data->uuid)) {
		$query = Connection::getInstance()->query("SELECT * FROM tasks WHERE uuid=".$data->uuid." AND done=0");
		if($query->rowCount() == 1) {
			$task = $query->fetch(PDO::FETCH_ASSOC);
			$type = (isset($data->type)) ? $data->type : $task["type"];
			$content = (isset($data->content)) ? $data->content : $task["content"];
			$sort_order = (isset($data->sort_order)) ? $data->sort_order : $task["sort_order"];
			$done = (isset($data->done)) ? $data->done : $task["done"];
			if($done == 0) {
				Connection::getInstance()->query("UPDATE tasks SET sort_order = sort_order + 1 WHERE sort_order >= ".$sort_order." AND done=".$done."");
			}
			Connection::getInstance()->query("UPDATE tasks SET type='".$type."' , content='".$content."' , sort_order=".$sort_order." , done=".$done." WHERE uuid=".$data->uuid."");
		}
		else {
			$json = array("error"=>"Are you a hacker or something? The task you were trying to edit doesn't exist.");
			echo json_encode($json);
		}
	}
	else {
		$json = array("error"=>"Do you need to say UUID Task!");
		echo json_encode($json);
	}
});

$app->run();
