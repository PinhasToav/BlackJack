import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  /**
   * isAuth will return the status of the use
   */
  isAuth(){
    // If there is local storage variable, user logged in (another check will be in the backend)
    if ( this.getLocalStorage() !== null ) return true;
    // Otherwise, false
    return false;
  }

  /**
   * Generate random string for the user authenticated.
   * @param random_string string  The random string we have back from the backend.
   */
  public setLocalStorage(random_string) {
    // In case of local storage, clear it so we will be able to start over
    if( this.getLocalStorage() === null ) localStorage.clear();

    // Store the local storage variable
    localStorage.setItem("user", random_string);
  }

  /**
   * Returns the cookie value.
   */
  public getLocalStorage(){
    return localStorage.getItem("user");
  }
}
