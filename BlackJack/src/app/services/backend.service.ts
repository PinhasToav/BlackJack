import {Injectable, Output} from '@angular/core';
import {BehaviorSubject} from "rxjs";
/**
 * The backend service will be responsible for all the backend calls.
 */
@Injectable({
  providedIn: 'root'
})
export class BackendService {
  // The URL we able to call (Rest API)
  private url = 'http://blackjack.com/blackjackback/controller/';
  // XHR object
  private http = new XMLHttpRequest();
  // The user name and email we are passing to the next component
  @Output() user_source = new BehaviorSubject<Object>({});
  current_user = this.user_source.asObservable();

  // User initialed
  private user_initialed = false;

  /**
   * Will stand for every backend call.
   * @param class_name  string  The class name we call,
   * @param method_name string  The method name we call,
   * @param data        string  The data we send,
   * @return  string  The data we get back from the backend.
   */
  private callBackend(class_name, method_name, data){
    // The returned data
    let data_to_return;

    this.http.open("POST", this.url+class_name+"/"+method_name, false);

    // Send the proper header information along with the request
    this.http.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    // Call a function when the state changes
    this.http.onreadystatechange = function() {
      // Status done?
      if( this.readyState === 4 ) {
        // Is it successfully done?
        if( this.status === 200 ) {
          console.log("Successfully done!");
          // Gets the response data
          data_to_return = JSON.parse(this.responseText);
        }
      }
    }
    this.http.send(data);

    // return the data
    return data_to_return;
  }

  /**
   * Format the data.
   * @param data  The data itself as Object.
   */
  private setData(data) {
    // Formatted data holder
    let holder = '';

    // Loop through the data keys and values
    for (let [key, value] of Object.entries(data)) {
      // Add the data formatted
      holder = holder.concat(`${key}=${value}&`);
    }
    // Remove the last `&`
    holder = holder.substring(0, holder.length - 1);

    // Add the cookie value for each request
    holder = holder.concat(`&cookie=${localStorage.getItem("user")}`);

    // Return the data
    return holder
  }

  /**
   * Call login backend
   * @param data  The data as object
   */
  public login(data) {
    data = this.setData(data);
    let returned_data = this.callBackend('user', 'login', data);
    this.user_initialed = true;

    // Set the user data in the emitter to send it to the next component
    this.user_source.next({'user_id': returned_data['id'], 'full_name': returned_data['full_name']})

    // Return it
    return returned_data;
  }

  /**
   * Call register backend
   * @param data  The data as object
   */
  public register(data) {
    data = this.setData(data);
    return this.callBackend('user', 'register', data);
  }

  /**
   * Call the backend to initiate new board and gets the data right after.
   * @param data  The data as object.
   */
  public initialBoard(data) {
    data = this.setData(data);
    return this.callBackend('board', 'initboard', data);
  }

  /**
   * Call the backend to get another card for the user.
   * @param data  The data as object.
   */
  public hit(data) {
    data = this.setData(data);
    return this.callBackend('player', 'hit', data);
  }

  /**
   * Call the backend to change the user status to `waiting` status.
   * @param data  The data as object.
   */
  public stand(data) {
    data = this.setData(data);
    return this.callBackend('player', 'stand', data);
  }

  /**
   * Call the backend to double the bet amount and gets one more card.
   * Also, change the player's status (lose? waiting for the others to finish they'r turns?)
   * @param data  The data as object.
   */
  public double(data) {
    data = this.setData(data);
    return this.callBackend('player', 'double', data);
  }

  /**
   * Call the backend to surrender, the player will lose half of hes initial bet,
   * also the game will count as lose.
   * @param data  The data as object.
   */
  public surrender(data) {
    data = this.setData(data);
    return this.callBackend('player', 'surrender', data);
  }

  /**
   * Call the backend to split the cards.
   * @param data  The data as object.
   */
  public split(data) {
    data = this.setData(data);
    return this.callBackend('player', 'split', data);
  }

  /**
   * Call the backend to play virtual player, dealer and finish the game.
   * @param data  The data as object.
   */
  public playVirtualTurns(data) {
    data = this.setData(data);
    return this.callBackend('player', 'playVirtualTurns', data);
  }

  /**
   * Initial user if need so,
   * This function will be activate when user will refresh the page.
   * @param data  cookie data.
   */
  public initUserIfNeed(data) {
    // user_initialed true means we already have the information
    if( this.user_initialed ) return true;

    // Otherwise, gets the data
    data = this.setData(data);
    let returned_data = this.callBackend('cookie', 'getDataByCookie', data);

    // In case of invalid cookie ID
    if( !returned_data['success'] ) {
      // If we gets here we have cookie and it's not valid, unset it.
      localStorage.clear();
      return false;
    }

    // Otherwise, we successfully gets the information
    // Set the user data in the emitter to send it to the next component
    this.user_source.next({'user_id': returned_data[0]['id'], 'full_name': returned_data[0]['full_name']})

    // Return it
    return true;
  }

  /**
   * Call register backend
   * @param data  The data as object
   */
  public myAccount(data) {
    data = this.setData(data);
    return this.callBackend('user', 'myAccount', data);
  }
}
