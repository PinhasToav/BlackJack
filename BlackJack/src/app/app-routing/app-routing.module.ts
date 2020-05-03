import { NgModule } from '@angular/core';
import {Routes, RouterModule} from "@angular/router";
import {LoginComponent} from "../components/login/login.component";
import {BoardComponent} from "../components/login/board/board.component";
import {AuthGuard} from "../auth.guard";

const routes: Routes = [
  {path:'welcome', component:LoginComponent},
  {path:'game', component: BoardComponent, canActivate:[AuthGuard]},
  {path:'game', component: BoardComponent},
  {path:'', redirectTo:'/welcome', pathMatch: 'full'},
  {path:'**', redirectTo:'/welcome', pathMatch: 'full'}
];

@NgModule({
  declarations: [],
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }
export const routingComponent = [LoginComponent, BoardComponent]
