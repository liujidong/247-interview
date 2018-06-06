<?php

class PinterestService extends BaseService {
    
    public function __construct() {
        parent::__construct();
    }
    
    // input:
    // - pinterest_account_id
    // - account_dbobj
    // - job_dbobj
    // - (optional) force
    public function import_account_boards() {
        $pinterest_account_id = $this->params['pinterest_account_id'];
        $account_dbobj = $this->params['account_dbobj'];
        $force = isset($this->params['force'])?$this->params['force']:false;
            
        $pinterest_account = new PinterestAccount($account_dbobj);
        $pinterest_account->findOne('id='.$pinterest_account_id);
        
        if(!isPinterestAccountImported($pinterest_account) || $force) {
            Log::write(INFO, 'start importing pinterest account '.$pinterest_account->getId().' '.$pinterest_account->getUsername());
            $pinterest_username = $pinterest_account->getUsername();
            if(!$pinterest_account_info = get_pinterest_account_info($pinterest_username)) {
                $this->status = 1;
                $this->errnos[NO_BOARD_AVAILABLE] = 1;
                
                Log::write(INFO, 'Scraping the pinterest account boards failed '.$pinterest_account->getId().' '.$pinterest_account->getUsername());
                return;
            }

            Log::write(INFO, 'Pinterest account boards scraping info: '.json_encode($pinterest_account_info));
            $pinterest_account->setImageLargeUrl(isset($pinterest_account_info['user']['image_large_url'])?$pinterest_account_info['user']['image_large_url']:'');
            $pinterest_account->setImageUrl(isset($pinterest_account_info['user']['image_large_url'])?$pinterest_account_info['user']['image_large_url']:'');
            $pinterest_account->setWebsite(isset($pinterest_account_info['user']['website'])?$pinterest_account_info['user']['website']:'');
            $pinterest_account->setTwitterLink(isset($pinterest_account_info['user']['twitter_link'])?$pinterest_account_info['user']['twitter_link']:'');
            $pinterest_account->setLocation($pinterest_account_info['user']['location']);
            $pinterest_account->setFullName($pinterest_account_info['user']['full_name']);
            $pinterest_account->setAbout($pinterest_account_info['user']['about']);
            $pinterest_account->setBoards($pinterest_account_info['user']['stats']['boards_count']);
            $pinterest_account->setPins($pinterest_account_info['user']['stats']['pins_count']);
            $pinterest_account->setLikes($pinterest_account_info['user']['stats']['likes_count']);
            $pinterest_account->setFollowers($pinterest_account_info['user']['stats']['followers_count']);
            $pinterest_account->setFollowings($pinterest_account_info['user']['stats']['following_count']);

            $pinterest_account->save();
            Log::write(INFO, 'Created pinterest account '.$pinterest_account->getId().' '.$pinterest_account->getUsername());
            
            $boards = $pinterest_account_info['boards'];
            foreach($boards as $i=>$board) {
                $external_id = $board['id'];
                $board_obj = new PinterestBoard($account_dbobj);
                $board_obj->findOne("external_id='{$account_dbobj->escape($external_id)}'");
                $board_obj->setExternalId($board['id']);
                $board_obj->setName($board['name']);
                $board_obj->setUrl($board['url']);
                $board_obj->setThumbnails(join(',', $board['thumbnails']));
                $board_obj->setPins($board['stats']['pins_count']);

                $board_obj->save();
                Log::write(INFO, 'Created pinterest board '.$board_obj->getId());
                BaseMapper::saveAssociation($pinterest_account, $board_obj, $account_dbobj);
                Log::write(INFO, 'Created association bw pinterest accounts boards '.$pinterest_account->getId().' '.$board_obj->getId());
                
                // create a job for uploading boards thumbnails
//                $job3 = new Job($job_dbobj);
//                $job3->setType(PINTEREST_IMAGE_UPLOADER);
//                $job3->setData(array(
//                    'type' => 'boards',
//                    'id' => $board_obj->getId()
//                ));
//                $job3->setHash1();
//                $job3->save();
//                Log::write(INFO, 'Created Pinterest Image Uploading job boards '.$board_obj->getId());
            }
        }
        
        $this->status = 0;
        $this->response = $pinterest_account_info;
    }
    
   // input: board id, account_dbobj
    public function import_board_pins() {
        $board_id = $this->params['board_id'];
        $account_dbobj = $this->params['account_dbobj'];
        
        $board = new PinterestBoard($account_dbobj);
        $board->findOne('id='.$board_id);
        $board_external_id = $board->getExternalId();
        $boards = new PinterestBoardPage($board_external_id);

        while ($pins = $boards->getNext()) {
            foreach($pins as $pin) {
                $pin_obj = new PinterestPin($account_dbobj);
                $pin_obj->setExternalId($pin['id']);
                $pin_obj->setDomain($pin['domain']);
                //$pin_obj->setSource($pin['source']);
                //$pin_obj->setIsRepin($pin['is_repin']);
                $pin_obj->setDescription($pin['description']);
                $pin_obj->setImagesBoard($pin['image_192']);
                $pin_obj->setImagesMobile($pin['image_550']);
                $pin_obj->setImagesCloseup($pin['image_192']);
                $pin_obj->setImagesThumbnail($pin['image_45']);
                $pin_obj->setLikes($pin['counts']['likes']);
                //$pin_obj->setComments($pin['counts']['comments']);
                $pin_obj->setRepins($pin['counts']['repins']);
                //$pin_obj->setPrice(empty($pin['price'])?0:$pin['price']);

                $pin_obj->save();
                Log::write(INFO, 'Create a pin object '.$pin_obj->getId());
                BaseMapper::saveAssociation($board, $pin_obj, $account_dbobj);
                Log::write(INFO, 'Created the association bw the boards and pins '.$board_id.' '.$pin_obj->getId());
                $this->response[]['pin'] = $pin_obj;

                // create a job for uploading pin images
//                $job3 = new Job($job_dbobj);
//                $job3->setType(PINTEREST_IMAGE_UPLOADER);
//                $job3->setData(array(
//                    'type' => 'pins',
//                    'id' => $pin_obj->getId()
//                ));
//                $job3->setHash1();
//                $job3->save();
//                Log::write(INFO, 'Created Pinterest Image Uploading job pins '.$pin_obj->getId());
            }
        }
        $this->status = 0;
    }
    
}


