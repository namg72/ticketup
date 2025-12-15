export interface TicketCategory{
  id: number
  name: string
  description?: string
  active?: boolean

}
export interface TicketCategoryForm {
 
  id?: number
  name: string | null
  description?: string | null
  active?: boolean | null

  

}

