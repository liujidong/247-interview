<?php
class FeedbackService extends BaseService {
    
    public function createReview(){
        
        $review_score = $this->params['review_score'];
        $review_content = $this->params['review_content'];
        if(empty($review_score))
            $this->errnos['INVALID_FEEDBACK_ERROR'] = 1;
        $shopper_id = $this->params['shopper_id'];
        $order_id = $this->params['order_id'];
        $store_dbobj = $this->params['store_dbobj'];
        
        if(empty($this->errnos)){
            
            $review = new Review($store_dbobj);
            
            $review->setShopper_id($shopper_id);
            $review->setOrder_id($order_id);
            $review->setScore($review_score);
            $review->setText($review_content);
            
            $review->save();
        }
    }
    
    public function getReview(){
        $store_dbobj = $this->params['store_dbobj'];
        $reviews = array();
        $reviews = ReviewsMapper::getReviews($store_dbobj);
        $this->response = $reviews;
    }
}