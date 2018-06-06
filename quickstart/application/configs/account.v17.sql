
ALTER TABLE `pin_images` ADD UNIQUE KEY pinterest_pin_id (pinterest_pin_id);

ALTER TABLE `pin_images` ADD KEY image_45 (image_45);

update version set version=17;