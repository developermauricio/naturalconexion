<!-- start Simple Custom CSS and JS -->
<script type="text/javascript">
 





jQuery(document).ready(function( $ ){
  	const validator = RegExp("^[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$");
  	let disableBtn = true;
  	const msgUser = document.getElementById('msgUser');
  	const btnForm = document.getElementById('invitado');
  	const emailInput = document.getElementById('myemail');
    	
  	const urlApi = 'https://naturalconexion.com/wp-json/wp/v2/user-email/';
  
    
  	const queryEmail = async ( emailUser ) => {
        let emailResponse = await jQuery.ajax({
            url: urlApi + emailUser,
            type: "GET",            
        });
      //console.log('this email: ', email)
      return emailResponse.res;
    }
        
    const changeInputEmail =  async (e) => {
      if ( validator.test(e.target.value) ) {
        let resp = await queryEmail(e.target.value);
        //console.log('resp: ', resp)
        if ( resp ) {
          console.log('el email esta registrado')
          msgUser.style.display = 'block';
          btnForm.style.display = 'none';
        } else {
          console.log('el email es nuevo')
          msgUser.style.display = 'none';
          btnForm.style.display = 'block';
        }
      } else {
        console.log('email no valido: ', e.target.value)
      }      
    }
  
    emailInput.addEventListener('change', changeInputEmail);
    
});

</script>
<!-- end Simple Custom CSS and JS -->
