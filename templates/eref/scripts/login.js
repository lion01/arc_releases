window.addEvent('domready', function() {
    if( $E( '#form-login fieldset.input' ) != null ) {
        $E( '#form-login fieldset.input' ).getElement( 'input[type=submit]' ).addClass( 'btn' );
        $E( '#form-login fieldset.input #modlgn_username' ).focus();
    }
    if( $E( '#com-form-login fieldset.input' ) != null ) {
        $E( '#com-form-login fieldset.input' ).getElement( 'input[type=submit]' ).addClass( 'btn' );
        $E( '#com-form-login fieldset.input #modlgn_username' ).focus();
    }
    if( $E( '.form-validate button.button.validate' ) != null ) {
        $E( '.form-validate button.button.validate' ).getElement( 'button[type=submit]' ).addClass( 'btn' );
    }
});