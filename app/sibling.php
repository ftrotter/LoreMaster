<?php
/*
Note: because this file was signed, everything originally placed before the name space line has been replaced... with this comment ;)
FILE_SIG=4ec5b72dcf0bb2dcd077ff1fd970e476
*/
namespace App;
/*
	sibling: controls DURC_aaa.sibling

This class started life as a DURC model, but itwill no longer be overwritten by the generator
this is safe to edit.


*/
class sibling extends \App\DURC\Models\sibling
{
	//this controls what is downloaded in the json for this object under card_body.. 
	//this function returns the html snippet that should be loaded for the summary of this object in a bootstrap card
	//read about the structure here: https://getbootstrap.com/docs/4.3/components/card/
	//this function should return an html snippet to go in the first 'card-body' div of an HTML interface...
	public function getCardBody() {
		return parent::getCardBody(); //just use the standard one unless a user over-rides this..
	}


	//You may need to change these for 'one to very very many' relationships.
/*
		protected $DURC_selfish_with = [ 
			'step_sibling', //from from many
			'sibling', //from from many
		];

*/
	//you can uncomment fields to prevent them from being serialized into the API!
	protected  $hidden = [
			//'id', //int
			//'siblingname', //varchar
			//'step_sibling_id', //int
			//'sibling_id', //int
			//'created_at', //datetime
			//'updated_at', //datetime
		]; //end hidden array


//DURC HAS_MANY SECTION

/**
*	DURC is handling the step_sibling for this sibling in sibling
*       but you can extend or override the defaults by editing this function...
*/
	public function step_sibling(){
		return parent::step_sibling();
	}


/**
*	DURC is handling the sibling for this sibling in sibling
*       but you can extend or override the defaults by editing this function...
*/
	public function sibling(){
		return parent::sibling();
	}


//DURC BELONGS_TO SECTION

		//DURC would have added step_sibling but it was already used in has_many. 
		//You will have to resolve these recursive relationships in your code.
		//DURC would have added sibling but it was already used in has_many. 
		//You will have to resolve these recursive relationships in your code.

	//look in the parent class for the SQL used to generate the underlying table

	//add fields here to entirely hide them in the default DURC web interface.
        public static $UX_hidden_col = [
        ];

        public static function isFieldHiddenInGenericDurcEditor($field){
                if(in_array($field,self::$UX_hidden_col)){
                        return(true);
                }
        }

	//add fields here to make them view-only in the default DURC web interface
        public static $UX_view_only_col = [
        ];

        public static function isFieldViewOnlyInGenericDurcEditor($field){
                if(in_array($field,self::$UX_view_only_col)){
                        return(true);
                }
        }

	//your stuff goes here..
	

}//end sibling