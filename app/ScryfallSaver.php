<?php

namespace App;

/*
       Understands to take a list of cards (could be just one) 
	and save them (trampling) to the DB.

*/


class  ScryfallSaver {

		//the idea of the card_field_mirror and the cardface_field_mirror is that generally, we just want to put 
		//the typical per-card data into either a cardface or a card.. and rather than manually copying each value
		//we just loop over the values and say "Just put this data directly into the database as it appears in the JSON
		//while other values we might want to massage the data, or perhaps change the column name or do something else...
		//these values can just be safely saved straight to the database.
                public static  $card_field_mirror = [
                        'lang',
			'released_at',
                        'oracle_id',
                        'rulings_uri',
                        'layout',
                        'rarity',
                        'set_name',
                        'set_type',
                        ];

                public static $cardface_field_mirror = [
                        'artist',
			'cmc',
                        'flavor_text',
                        'border_color',
                        'type_line',
                        'mana_cost',
                        'name',
                        'oracle_text',
                        'border_color',
                        'power',
			'toughness',
                        'cardface_index', // I am building this one below...
                ];

                public static $cardface_convert_to_tiny_int = [
                        'foil',
                        'nonfoil',
                        'oversized',
                        ];

                public static $card_convert_to_tiny_int = [
                        'reserved',
                        'reprint',
                        'variation',
                        'promo',
                        'story_spotlight',
                ];

                public  static $is_legal = [
                        'standard',
                        'future',
			'historic',
			'gladiator',
			'pioneer',
			'explorer',
                        'modern',
                        'legacy',
                        'pauper',
                        'vintage',
                        'penny',
                        'commander',
			'brawl',
			'historicbrawl',
			'alchemy',
			'paupercommander',
                        'duel',	
                        'oldschool',
                        'premodern',
                ];

                //set this so that we can uses less / greater than to see if legal
                public static $legal_lookup = [
                        'banned' => -2,
                        'not_legal' => -1,
                        'restricted' => 1,
                        'legal' => 2,
                ];

                public static $color_lookup = [
                        'G' => 'green',
                        'R' => 'red',
                        'U' => 'blue',
                        'B' => 'black',
                        'W' => 'white',
                ];

                public static $image_lookup = [
                        'small',
                        'normal',
                        'large',
                        'png',
                        'art_crop',
                        'border_crop',
                ];

		public static $pricetype_lookup  = [
			'usd' => 1,
			'usd_foil' => 2,
			'eur' => 3,
			'tix' => 4,
			'eur_foil' => 5,
			'usd_etched' => 6,
		];


	public static function starttime(){
		//based on https://withdave.com/2010/09/benchmarking-testing-php-script-microtime/
		$r = explode( ' ', microtime() );
		$r = $r[1] + $r[0];
		return $r;

	}


	public static function endtime($starttime){

		$r = explode( ' ', microtime() );
		$r = $r[1] + $r[0];
		$r = round($r - $starttime,4);
		return $r;

	}



	public static  function saveCardList($cards){

		$is_new_legality = false;
		$new_legality_list = [];


		foreach($cards as $this_outer_card){
			//first we consider the cardfaces...

			$card_start_time = self::starttime();

	
			$card_loop = [];
			if(isset($this_outer_card['card_faces'])){
				//then this card has more than one card face..
				$name = $this_outer_card['name'];
				echo "Looping over $name\n";
				$is_double_face = true;
				foreach($this_outer_card['card_faces'] as $cardface_index => $this_face){
					$this_face['cardface_index'] = $cardface_index;
					$card_loop[] = array_merge($this_face,$this_outer_card); //will flatten the card face and the card into one big thing...
				}
			}else{
				$is_double_face = false;
				$this_outer_card['cardface_index'] = 0; //because it just has the one..
				$card_loop[] = $this_outer_card; //we only have 1 card <-> cardface
			}	
	
			foreach($card_loop as $this_card){
				
				if(isset($this_card['illustration_id'])){	
					$illustration_id = $this_card['illustration_id'];
				}else{
					$illustration_id = 0;
				}
				$scryfall_id = $this_card['id'];
				$cardface_index = $this_card['cardface_index'];
	
				if($is_double_face){
	
					echo "\tscrayfall_id:$scryfall_id	\tcardface_index:$cardface_index \tillustration_id:$illustration_id\n";		
					
				}
	
				$card_fill = []; //this should be the same for both card loops...
				$cardface_fill = []; //blank this out for every card loop... 
	
				//these we rename..
				$card_fill['scryfall_id'] = $scryfall_id;

				$this_set = $this_card['set'];
				$MTGSet = \App\mtgset::where('code', $this_set)->first();
				if(is_null($MTGSet->id)){
					echo "Looking up the id for set $this_set failed";
					exit();
				}	
				$mtgset_id = $MTGSet->id;
				$card_fill['mtgset_id'] = $mtgset_id;
				$card_fill['collector_number'] = $this_card['collector_number']; //confusingly this is a string and not an INT field. 
				$card_fill['scryfall_api_uri'] = $this_card['uri'];		
				$card_fill['scryfall_web_uri'] = $this_card['scryfall_uri'];
	
				if(isset($this_card['variation_of'])){
					$card_fill['variation_of_scryfall_id'] = $this_card['variation_of'];
				}else{
					$card_fill['variation_of_scryfall_id'] = null;
				}
	
				foreach(self::$card_convert_to_tiny_int as $is_postfix){
					if(isset($this_card[$is_postfix])){
						if($this_card[$is_postfix]){
							$card_fill["is_$is_postfix"] = 1;
						}else{
							$card_fill["is_$is_postfix"] = 0;
						}
					}else{
						$card_fill["is_$is_postfix"] = 0;//missing values get zero
					}
				}
	
				//
				foreach(self::$card_field_mirror as $this_field){
					if(isset($this_card[$this_field])){
						$card_fill[$this_field] = $this_card[$this_field]; //pretty obvious
					}else{
						$card_fill[$this_field] = null; //should be accepted by the DB
					}
				}
	
				//flatten the legalities i.e. legacy, modern and standard
				//The lagalities change too much and this part of the script would generate a column for data that we have not designed...
				//which would crash things. Now modifying to make sure the field exists before prsuming to save it.
				//this creates a chore of occasionally modifying the list in is_legal above and also adding space in the card table for new legalities
				if(isset($this_card['legalities'])){
					foreach($this_card['legalities'] as $legal_in => $legal_status){
						$legal_score = self::$legal_lookup[$legal_status];
						if(in_array($legal_in,self::$is_legal)){	
							$card_fill["legal_$legal_in"] = $legal_score; //it is 0 by dfault..
						}else{
							//getting hear means there is a new legality..
							$is_new_legality = true;
							$new_legality_list[] = $legal_in;		
						}
					}
				}
	
				//flattent the games array i.e. mtgo, arena, print
				if(isset($this_card['games'])){
					foreach($this_card['games'] as $this_game){
						// There's also this awful 'sega' game type that needs accounting for...
						if(($this_game == 'sega') or ($this_game == 'astral')){
							echo "SEGA!";
						}else{
							$card_fill["is_game_$this_game"] = 1; //it is 0 by dfault..
						}
					}
				}
	
				//we consider each card unique in its set...
				$DURC_card = \App\card::firstOrNew(['scryfall_id' => $scryfall_id,'mtgset_id' => $mtgset_id ]);
				$DURC_card->fill($card_fill);
				$DURC_card->save();
	
				$card_id = $DURC_card->id; //need this to save the card face..
			
				$cardface_fill['card_id'] = $card_id;
				
				//first lets copy over the fields that will not change at all.. 
				foreach(self::$cardface_field_mirror as $this_field){
					if(isset($this_card[$this_field])){
						$cardface_fill[$this_field] = $this_card[$this_field]; //pretty obvious
					}else{
						$cardface_fill[$this_field] = null; //should be accepted by the DB
					}
				}
			
			 	//convert the booleans to our is_ tinyint notation...
				foreach(self::$cardface_convert_to_tiny_int as $is_postfix){
					if(isset($this_card[$is_postfix])){
						if($this_card[$is_postfix]){
							$cardface_fill["is_$is_postfix"] = 1;
						}else{
							$cardface_fill["is_$is_postfix"] = 0;
						}
					}else{
						$cardface_fill["is_$is_postfix"] = 0;//missing values get zero
					}
				}
	
				//now we calculate our is_color variables...
				$has_color = false;
				$color_identity_count = 0;
				foreach($this_card['color_identity'] as $has_this_color){
					$color_name = self::$color_lookup[$has_this_color];
					$cardface_fill["is_color_identity_$color_name"] = 1;				
	
					$color_identity_count++;
				}
			
				$color_count = 0;
				foreach($this_card['colors'] as $has_this_color){
					$color_name = self::$color_lookup[$has_this_color];
					$cardface_fill["is_color_$color_name"] = 1;				
	
					$color_count++;
					$has_color = true;
				}

				if(!$has_color){
					$cardface_fill["is_colorless"] = true;
				}
	
	
				$cardface_fill['color_count'] = $color_count;
				$cardface_fill['color_identity_count'] = $color_identity_count;


				if(isset($this_card['image_uris'])){	
					foreach($this_card['image_uris'] as $image_type => $image_url){
						$cardface_fill["image_uri_$image_type"] = $image_url;
					}

					$cardface_fill['image_uri'] = $cardface_fill["image_uri_art_crop"];

				}

				//set  the default url...


				//a card face is unique on card_id plus illustration_id... probably...
				$DURC_cardface = \App\cardface::firstOrNew([
								'card_id' => $card_id,
								'illustration_id' => $illustration_id,
								]);
								$DURC_cardface->fill($cardface_fill);
				$DURC_cardface->save();
				$this_cardface_id = $DURC_cardface->id;

				//now we save all of the things that are "many" to the cardface or cards

				//could be zero or many..
				$multiverse_ids = $this_card['multiverse_ids'];

				foreach($multiverse_ids as $this_multiverse_id){
					$mverseObj = \App\mverse::firstOrNew([
						'cardface_id' => $this_cardface_id,
						'multiverse_id' => $this_multiverse_id,
						]);

					$mverseObj->gatherer_url = "https://gatherer.wizards.com/Pages/Card/Details.aspx?multiverseid=$this_cardface_id"; //hardcoded = ok for now..
					$mverseObj->save();
				}


				$prices  = $this_card['prices'];
				
				foreach($prices as $this_type => $price_amount){
					$priceObj = new \App\cardprice;
					$priceObj->price = $price_amount;
					$priceObj->card_id = $card_id;
					$priceObj->scryfall_id = $scryfall_id;
					$priceObj->pricetype_id = self::$pricetype_lookup[$this_type]; 
					$priceObj->save();	
				}
	
			} //end of cardface loop
	
			$time_to_save_this_card = self::endtime($card_start_time);

			echo "time to save this card: $time_to_save_this_card\n";


		} //end of card loop

		//should there be better error logging? yes, there should.
		if($is_new_legality){
			echo "Warning: new legalities found!!";	
			var_export($new_legalities_list);
		}


	}



}
