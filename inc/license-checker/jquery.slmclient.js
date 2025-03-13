/**
 * Software License Manager Client
 *
 * @package    Software License Manager Client
 * @subpackage jquery.simclient.js
/*  Copyright (c) 2019- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; version 2 of the License.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

jQuery(
	function($) {

		$( '#activate_license' ).on(
			'click' ,
			function() {
				license_key = $( '#' + SLMCLIENTCHARGE.license_key_name ).val();
				chargeServer( license_key );
			}
		)

		/* Charge Server */
		function chargeServer( license_key ) {
			$( '.bldm_slm_text_message' ).html('Please wait ...');
			$.ajax(
				{
					type: 'POST',
					dataType: 'json',
					url: SLMCLIENTCHARGE.ajax_url,
					data: {
						'action': SLMCLIENTCHARGE.action,
						'nonce': SLMCLIENTCHARGE.nonce,
						'special_secretkey' : SLMCLIENTCHARGE.special_secretkey,
						'license_key' : license_key,
						'item_reference': SLMCLIENTCHARGE.item_reference,
						'license_server_url' : SLMCLIENTCHARGE.license_server_url,
						'license_key_name' : SLMCLIENTCHARGE.license_key_name
					}
				}
			).done(
				function( callback ){
					$( '.bldm_slm_text_message' ).empty();
					$( '.bldm_slm_text_message' ).append( SLMCLIENTCHARGE.slm_text_message );
					$( '#' + SLMCLIENTCHARGE.license_key_name ).attr( 'readonly', 'readonly' );
					$( '#activate_license' ).remove();
					
					location.reload();
					
				}
			).fail(
				function( XMLHttpRequest, textStatus, errorThrown ) {
					console.log( "XMLHttpRequest : " + XMLHttpRequest.status );
					console.log( "textStatus     : " + textStatus );
					console.log( "errorThrown    : " + errorThrown.message );
				}
			);
		}
	}
);
