 const verPass = document.getElementById('verPass');
    const pass = document.getElementById('password');
    verPass.addEventListener('click', () => {
      const visible = pass.type === 'text';
      pass.type = visible ? 'password' : 'text';
      verPass.textContent = visible ? 'Ver' : 'Ocultar';
    });

    document.getElementById('formLogin').addEventListener('submit', (e) => {
      e.preventDefault();
      
      const user = document.getElementById("usuario").value;
      const password = document.getElementById("password").value;

      $.ajax({
        url: "/Peluqueria/login/php/validarLogin.php",
        method:"POST",
        data:{user : user,
              password : password
        },
        dataType:'json',
        success: function(response){
          if(response.success){
           window.location.href = "/Peluqueria/dashboard/";
          }
          else {
          Swal.fire({
          icon: "error",
          title: "Error!",
          text: response.message
        });
    }
        }
      });


    });
