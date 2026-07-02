import { type PaginationMeta } from "../api/events";

type PaginationProps = {
  meta: PaginationMeta;
  onPageChange: (page: number) => void;
  onLimitChange: (limit: number) => void;
};

const limitOptions = [10, 20, 50];

export function Pagination({ meta, onPageChange, onLimitChange }: PaginationProps) {
  const { page, pages, limit } = meta;

  return (
    <div className="pagination">
      <button type="button" disabled={page <= 1} onClick={() => onPageChange(page - 1)}>
        Previous
      </button>

      <span>
        Page {page} of {Math.max(pages, 1)}
      </span>

      <button type="button" disabled={page >= pages} onClick={() => onPageChange(page + 1)}>
        Next
      </button>

      <label htmlFor="page-size">
        Per page
        <select
          id="page-size"
          value={limit}
          onChange={(e) => onLimitChange(Number(e.target.value))}
        >
          {limitOptions.map((option) => (
            <option key={option} value={option}>
              {option}
            </option>
          ))}
        </select>
      </label>
    </div>
  );
}
