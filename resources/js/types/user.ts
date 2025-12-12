export interface User {
  id: number
  name: string
  supervisor_id: number | null
  email: string
  is_active: boolean

}
export interface UserForm {
 
  id?: number
  supervisor_id: number | null
  name: string | null
  email: string | null
  is_active?: boolean | null
  password?: string 
  role_name: 'employee' | 'supervisor'
  

}

