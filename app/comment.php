<?php
/*
Note: because this file was signed, everything originally placed before the name space line has been replaced... with this comment ;)
FILE_SIG=dfeefeebe3bcf085d4952ef34af1b02d
*/
namespace App;
/*
	comment: controls mirrulation.comment

This class started life as a DURC model, but itwill no longer be overwritten by the generator
this is safe to edit.


*/
class comment extends \App\DURC\Models\comment
{
	//this controls what is downloaded in the json for this object under card_body.. 
	//this function returns the html snippet that should be loaded for the summary of this object in a bootstrap card
	//read about the structure here: https://getbootstrap.com/docs/4.3/components/card/
	//this function should return an html snippet to go in the first 'card-body' div of an HTML interface...
	public function getCardBody() {

		$commentId = $this->commentId;
		$comment_url = "https://www.regulations.gov/comment/$commentId";
		$comment_link = "<a href='$comment_url' target='_blank'>$commentId</a>";
		$comment_snippet = substr($this->comment_text,0,200);


		$comment_html = "<h5 class='card-title'>Regulations.gov Link: $comment_link</h5>
			<div class='card-body'><small>$comment_snippet</small></div>
		";

		return($comment_html);

	}


	//You may need to change these for 'one to very very many' relationships.
/*
		protected $DURC_selfish_with = [ 
			'unique_comment_cluster', //from from one
			'other_unique_comment_cluster', //from from one
		];

*/
	//you can uncomment fields to prevent them from being serialized into the API!
	protected  $hidden = [
			//'id', //int
			//'commentId', //varchar
			//'comment_on_documentId', //varchar
			//'comment_date_text', //varchar
			//'comment_date', //datetime
			//'comment_text', //longtext
			//'simplified_comment_text', //longtext
		]; //end hidden array


//DURC HAS_MANY SECTION
			//DURC did not detect any has_many relationships
//DURC BELONGS_TO SECTION
			//DURC did not detect any belongs_to relationships

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
	

}//end comment
