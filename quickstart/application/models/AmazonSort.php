<?php

// check http://docs.aws.amazon.com/AWSECommerceService/latest/DG/USSortValuesArticle.html#USSortValuesArticle_All
// to find out what Sort variable is available to each SearchIndex

class AmazonSort {
    
    // when SearchIndex: Apparel
    const relevancerank = 'relevancerank';
    const salesrank = 'salesrank';
    const pricerank = 'pricerank';
    const inverseprice = 'inverseprice';
    const _launch_date = '-launch-date';
    
    // when SearchIndex: Appliances
    const pmrank = 'pmrank';
    const price = 'price';
    const _price = '-price';
    const reviewrank = 'reviewrank';
    
    // when SearchIndex: ArtsAndCrafts
    
    // when SearchIndex: Automotive
    const titlerank = 'titlerank';
    const _titlerank = '-titlerank';
 
    // when SearchIndex: Baby
    
    // when SearchIndex: Beauty
    
    // when SearchIndex: Books
    const daterank = 'daterank';
    const inverse_pricerank = 'inverse-pricerank';
 
    // when SearchIndex: Classical
    const orig_rel_date = 'orig-rel-date';
    const _orig_rel_date = '-orig-rel-date';
    const releasedate = 'releasedate';
    const _releasedate = '-releasedate';
    
    // when SearchIndex: Collectibles
    
    // when SearchIndex: DigitalMusic
    
    // when SearchIndex: DVD
    const _video_release_date = '-video-release-date';
 
    // when SearchIndex: Electronics
    
    // when SearchIndex: Grocery
    const launch_date = 'launch-date';
    
    // when SearchIndex: HealthPersonalCare
    
    // when SearchIndex: HomeGarden
    
    // when SearchIndex:Industrial
    
    // when SearchIndex: Jewelry
    
    // when SearchIndex: KindleStore
    const _edition_sales_velocity = '-edition-sales-velocity';
    
    // when SearchIndex: Kitchen
    
    // when SearchIndex: LawnAndGarden
    
    // when SearchIndex: Magazines
    const subslot_salesrank = 'subslot-salesrank';
    
    // when SearchIndex: Marketplace
    
    // when SearchIndex: Merchants
    
    // when SearchIndex: Miscellaneous
    
    // when SearchIndex: MobileApps
    
    // when SearchIndex: MP3Downloads
    
    // when SearchIndex: Music
    const artistrank = 'artistrank';
    const release_date = 'release-date';
    
    // when SearchIndex: MusicalInstruments
    
    // when SearchIndex: MusicTracks
    
    // when SearchIndex: OfficeProducts
    
    // when SearchIndex: OutdoorLiving
    
    // when SearchIndex: PCHardware
    
    // when SearchIndex: PetSupplies
    const _pmrank = '+pmrank';
    
    // when SearchIndex: Photo
    
    // when SearchIndex: Shoes
    
    // when SearchIndex: Software
    
    // when SearchIndex: SportingGoods
    
    // when SearchIndex: Tools
    
    // when SearchIndex: Toys
    const _age_min = '-age-min';
    
    // when SearchIndex: UnboxVideo
    
    // when SearchIndex: Video
    
    // when SearchIndex: VideoGames
    
    // when SearchIndex: Watches
    
    // when SearchIndex: Wireless
    
    // when SearchIndex: WirelessAccessories
    
}
