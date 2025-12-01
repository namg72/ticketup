export interface User {
  id: number
  name: string
  supervisor_id: string 
  email: string
  is_active: boolean

}
export interface UserForm {
 
  supervisor_id: string 
  name: string
  email: string
  is_active?: boolean
  password: string

}

