
DELIMITER $$

CREATE
        TRIGGER `products_after_update` AFTER UPDATE
        ON `products`
        FOR EACH ROW BEGIN

        set @object_type = (select concat(@db_name := DATABASE(), "-", "products"));
        set @create_time = (select now());

        INSERT INTO {{account_dbname}}.change_logs (object_type, object_id, created) VALUES (@object_type, NEW.id, @create_time);

END $$

CREATE
        TRIGGER `products_after_insert` AFTER INSERT
        ON `products`
        FOR EACH ROW BEGIN

        set @object_type = (select concat(@db_name := DATABASE(), "-", "products"));
        set @create_time = (select now());

        INSERT INTO {{account_dbname}}.change_logs (object_type, object_id, created) VALUES (@object_type, NEW.id, @create_time);

END $$

CREATE
        TRIGGER `pictures_after_update` AFTER UPDATE
        ON `pictures`
        FOR EACH ROW BEGIN

        set @object_type = (select concat(@db_name := DATABASE(), "-", "pictures"));
        set @create_time = (select now());

        INSERT INTO {{account_dbname}}.change_logs (object_type, object_id, created) VALUES (@object_type, NEW.id, @create_time);

END $$

CREATE
        TRIGGER `pictures_after_insert` AFTER INSERT
        ON `pictures`
        FOR EACH ROW BEGIN

        set @object_type = (select concat(@db_name := DATABASE(), "-", "pictures"));
        set @create_time = (select now());

        INSERT INTO {{account_dbname}}.change_logs (object_type, object_id, created) VALUES (@object_type, NEW.id, @create_time);

END $$

CREATE
        TRIGGER `pictures_after_delete` AFTER DELETE
        ON `products_pictures`
        FOR EACH ROW BEGIN

        set @object_type = (select concat(@db_name := DATABASE(), "-", "products"));
        set @create_time = (select now());

        INSERT INTO {{account_dbname}}.change_logs (object_type, object_id, created) VALUES (@object_type, OLD.product_id, @create_time);

END $$

CREATE
        TRIGGER `categories_after_update` AFTER UPDATE
        ON `categories`
        FOR EACH ROW BEGIN

        set @object_type = (select concat(@db_name := DATABASE(), "-", "categories"));
        set @create_time = (select now());

        INSERT INTO {{account_dbname}}.change_logs (object_type, object_id, created) VALUES (@object_type, NEW.id, @create_time);

END $$

CREATE
        TRIGGER `categories_after_insert` AFTER INSERT
        ON `categories`
        FOR EACH ROW BEGIN

        set @object_type = (select concat(@db_name := DATABASE(), "-", "categories"));
        set @create_time = (select now());

        INSERT INTO {{account_dbname}}.change_logs (object_type, object_id, created) VALUES (@object_type, NEW.id, @create_time);

END $$

CREATE
        TRIGGER `categories_after_delete` AFTER DELETE
        ON `products_categories`
        FOR EACH ROW BEGIN

        set @object_type = (select concat(@db_name := DATABASE(), "-", "products"));
        set @create_time = (select now());

        INSERT INTO {{account_dbname}}.change_logs (object_type, object_id, created) VALUES (@object_type, OLD.product_id, @create_time);

END $$

CREATE
        TRIGGER `converted_pictures_after_update` AFTER UPDATE
        ON `converted_pictures`
        FOR EACH ROW BEGIN

        set @object_type = (select concat(@db_name := DATABASE(), "-", "converted_pictures"));
        set @create_time = (select now());

        INSERT INTO {{account_dbname}}.change_logs (object_type, object_id, created) VALUES (@object_type, NEW.id, @create_time);

END $$

CREATE
        TRIGGER `converted_pictures_after_insert` AFTER INSERT
        ON `converted_pictures`
        FOR EACH ROW BEGIN

        set @object_type = (select concat(@db_name := DATABASE(), "-", "converted_pictures"));
        set @create_time = (select now());

        INSERT INTO {{account_dbname}}.change_logs (object_type, object_id, created) VALUES (@object_type, NEW.id, @create_time);

END $$

DELIMITER ;

update version set version=21;
