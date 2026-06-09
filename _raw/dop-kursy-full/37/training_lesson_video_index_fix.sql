UPDATE `modx_partnerstraining_module_videos` mv
JOIN `modx_partnerstraining_lesson_videos` lv
  ON lv.`lesson_id` = mv.`lesson_id`
 AND lv.`sort_order` = 1
SET mv.`lesson_video_id` = lv.`id`
WHERE mv.`lesson_id` > 0
  AND mv.`lesson_video_id` = 0;

UPDATE `modx_partnerstraining_module_slides` ms
JOIN `modx_partnerstraining_lesson_videos` lv
  ON lv.`lesson_id` = ms.`lesson_id`
 AND lv.`sort_order` = 1
SET ms.`lesson_video_id` = lv.`id`
WHERE ms.`lesson_id` > 0
  AND ms.`lesson_video_id` = 0;

ALTER TABLE `modx_partnerstraining_module_videos`
  DROP INDEX `lesson_quality`,
  ADD UNIQUE KEY `lesson_video_quality` (`lesson_video_id`,`quality`);

ALTER TABLE `modx_partnerstraining_module_slides`
  DROP INDEX `lesson_slide`,
  ADD UNIQUE KEY `lesson_video_slide` (`lesson_video_id`,`slide_no`);
