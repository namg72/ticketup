export interface TicketUser {
  id?: number
  name?: string
  email?: string
}

export interface Ticket {
  id: number
  title: string 
  description: string | null
  total_amount: number 
  status: string | null
  need_revision: boolean
  created_at: string
  updated_at: string
  user: TicketUser
  category: String[]
  supervisor_id: string
  uri:string
  
}

export interface TicketForm {
  
  id: string,
  created_at: string
  title: string 
  description?: string | null
  total_amount: number 
  user_id?: number
  status: string | null
  supervisor_id: string
  category: any
  image: any
}


export interface  TicketRow  {
  id: number
  uri?: string | null
}

export interface MonthlyExpense {
    month: string;
    total: number;
}

export interface StatusCounts {
    pending: number;
    approved: number;
    review: number;
    rejected: number;
}

export  interface FiltersTickets {
    from?: string | null;
    to?: string | null;
    status?: string | null;
    supervisor_id?: number | null;
    user_name?: string | null;
    category_id?: number | null;
}