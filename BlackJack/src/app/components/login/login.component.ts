import {Component} from '@angular/core';
import {AppComponent} from "../../app.component";

@Component({
  selector: 'login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent extends AppComponent{
  // Variable to check if user clicked register, or login -
  // by default user see login first
  public isRegister = false;

  /**
   * Switch between register and login.
   */
  public clickedRegister(){
    this.isRegister = !this.isRegister;
  }

  /**
   * Call the backend to log in.
   */
  public callBackendLogin(){
    // Get the elements from the form
    let button = (<HTMLInputElement>document.getElementById('login_button'));
    let email = (<HTMLInputElement>document.getElementById('login_email'));
    let password = (<HTMLInputElement>document.getElementById('login_password'));

    // Make sure the email and password are valid
    if( email.checkValidity() && password.checkValidity() ) {
      // Call the backend
      let response = this.backend_service.login({'email': email.value, 'password': password.value});

      // Alert if user does not exist
      if (!response['success']) {
        // Change it's type to prevent early closing
        button.type = 'button';
        this.swal.fire({
          icon: 'error',
          title: 'Invalid email or password',
          text: 'Please, try again',
        })
        setTimeout(function () {
          // Change it's type to prevent early closing
          button.type = 'submit';
          }, 1);
      } else {
        // Successfully logged in
        this.auth_service.setLocalStorage(response['random_string']);

        // Navigate to the next component
        this.router.navigate(['game']);
      }
    }
  }

  /**
   * Call the backend to register.
   */
  public callBackendRegister(){
    // Get the elements from the form
    let button = (<HTMLInputElement>document.getElementById('register_button'));
    let email = (<HTMLInputElement>document.getElementById('register_email'));
    let full_name = (<HTMLInputElement>document.getElementById('register_full_name'));
    let password_1 = (<HTMLInputElement>document.getElementById('register_password1'));
    let password_2 = (<HTMLInputElement>document.getElementById('register_password2'));
    let age = (<HTMLInputElement>document.getElementById('register_age'));

    // Make sure the register parameters are valid
    if( email.checkValidity() && password_1.checkValidity() && password_2.checkValidity()
      && age.checkValidity() && full_name.checkValidity() ) {

      // Make sure the two passwords are equal
      if( password_1.value !== password_2.value ) {
        button.type = 'button';
        this.swal.fire({
          icon: 'error',
          title: 'Cannot sign up',
          text: 'User might be already exist',
        })
        setTimeout(function () {
          // Change it's type to prevent early closing
          button.type = 'submit';
        }, 1);
      } else {
        // Call the backend
        let response = this.backend_service.register(
          {
            'email': email.value,
            'full_name': full_name.value,
            'password_1': password_1.value,
            'password_2': password_2.value,
            'age': age.value
          }
        );
        // Alert if user does not exist
        if (!response['success']) {
          // Change it's type to prevent early closing
          button.type = 'button';
          this.swal.fire({
            icon: 'error',
            title: 'Cannot sign up',
            text: 'User might be already exist',
          })
          setTimeout(function () {
            // Change it's type to prevent early closing
            button.type = 'submit';
          }, 1);
        } else {
          // Change it's type to prevent early closing
          button.type = 'button';
          this.swal.fire({
            icon: 'success',
            title: 'Successfully registered',
            text: 'You can log in now',
          })
          setTimeout(function () {
            // Change it's type to prevent early closing
            button.type = 'submit';
          }, 1);
          this.clickedRegister();
        }
      }
    }
  }
}
