export const hand = {
  size: 2,
  first: 0,
  second: 1
}
export const status = {
  first: 0,
  second: 1
}
export const card = {
  height: "109px",
  width: "71px"
}
export const chip = {
  height: "110px",
  width: "110px"
}
export const dealer = {
  id: -2,
  first_hand : {card_position: {left:"46%", top:"1%"}},
}
export const virtual = {
  id: -1,
  first_hand : {card_position: {left:"53%", top:"43%"}, chip_position: {left:"62%", top:"20%"}},
  second_hand : {card_position: {left:"53%", top:"63%"}}
}
export const user = {
  first_hand : {card_position: {left:"37%", top:"43%"}, chip_position: {left:"32%", top:"20%"}},
  second_hand : {card_position: {left:"37%", top:"63%"}}
}
export const player = {
  user: 0,
  virtual: 1,
  dealer: 2
}

