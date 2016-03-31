<?php

$shooter = new PublicPostType("shooter");
$shooter->property1 = 1;
$shooter->property2 = 2;
$shooter->initLabels("стрелок","стрелков");

// ---

$shooterMetabox = new MetaBox($shooter->post_type);
$shooterMetabox->addField("birthday","Дата рождени","datetime",[
    "format" => "Y.M.D",
]);
$shooterMetabox->addField("sex","Пол","list",[
    "multiple" => true,
    "values" => ["Мужской","Женский"],
]);
$shooterMetabox->addField("city","Город","text");
$shooterMetabox->addField("name","Надпись","custom","path/to/file");
$shooterMetabox->addField("name","Надпись","custom",[
    "render" => "file OR callable",
    "action" => "file OR callable",
]);

// ---

