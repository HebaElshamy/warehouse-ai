import os
import numpy as np
import pandas as pd
from datetime import timedelta
from sqlalchemy import create_engine, text
from sklearn.ensemble import RandomForestRegressor

MODEL_VERSION = "rf_v1"


def make_features(df: pd.DataFrame) -> pd.DataFrame:
    """
    df columns: [product_id, date, qty]
    """
    df = df.sort_values(["product_id", "date"]).copy()

    for l in range(1, 8):
        df[f"lag_{l}"] = df.groupby("product_id")["qty"].shift(l)

    df["rolling_mean_7"] = (
        df.groupby("product_id")["qty"]
          .shift(1)
          .rolling(window=7, min_periods=1)
          .mean()
          .reset_index(level=0, drop=True)
    )

    df["day_of_week"] = df["date"].dt.dayofweek
    return df


def _fit_rf(train_hist: pd.DataFrame) -> RandomForestRegressor | None:
    feats = make_features(train_hist)
    feats = feats.dropna(subset=[f"lag_{l}" for l in range(1, 8)])
    if len(feats) < 15:
        return None

    feature_cols = [f"lag_{l}" for l in range(1, 8)] + ["rolling_mean_7", "day_of_week"]
    X = feats[feature_cols].values
    y = feats["qty"].values

    model = RandomForestRegressor(
        n_estimators=300,
        random_state=42,
        n_jobs=-1,
        min_samples_leaf=2
    )
    model.fit(X, y)
    return model


def train_and_forecast_sum(hist: pd.DataFrame, horizon_days: int) -> float:
    """
    Returns total predicted demand over next horizon_days (iterative multi-step)
    """
    hist = hist.sort_values("date").copy()
    last_date = hist["date"].max()

    model = _fit_rf(hist)

    # fallback لو بيانات قليلة
    if model is None:
        last7 = hist["qty"].tail(7)
        base = float(last7.mean()) if len(last7) else 0.0
        return max(base, 0.0) * horizon_days

    # series holds known + predicted
    series = hist.set_index("date")["qty"].astype(float).copy()

    total = 0.0
    for step in range(1, horizon_days + 1):
        d = last_date + timedelta(days=step)
        dow = d.dayofweek

        lags = []
        for l in range(1, 8):
            lag_date = d - timedelta(days=l)
            lags.append(float(series.get(lag_date, 0.0)))

        rolling_mean_7 = float(np.mean(lags[:7]))
        x_next = np.array(lags + [rolling_mean_7, dow], dtype=float).reshape(1, -1)

        yhat = float(model.predict(x_next)[0])
        yhat = max(yhat, 0.0)

        total += yhat
        series.loc[d] = yhat

    return total


def train_and_eval_mae(hist: pd.DataFrame, eval_days: int = 14) -> float | None:
    """
    Hold out last eval_days, train on earlier data, iterative predict eval_days,
    return MAE. If not enough data, return None.
    """
    hist = hist.sort_values("date").copy()

    # شرط بسيط عشان يبقى عندنا تدريب كافي + نافذة تقييم
    min_needed = eval_days + 8  # على الأقل 7 لاغ + نافذة تقييم
    if len(hist) < min_needed:
        return None

    cutoff_date = hist["date"].max() - timedelta(days=eval_days)
    train_hist = hist[hist["date"] <= cutoff_date].copy()
    test_hist  = hist[hist["date"] >  cutoff_date].copy()

    model = _fit_rf(train_hist)
    if model is None:
        return None

    last_train_date = train_hist["date"].max()
    series = train_hist.set_index("date")["qty"].astype(float).copy()

    preds = {}
    for step in range(1, eval_days + 1):
        d = last_train_date + timedelta(days=step)
        dow = d.dayofweek

        lags = []
        for l in range(1, 8):
            lag_date = d - timedelta(days=l)
            lags.append(float(series.get(lag_date, 0.0)))

        rolling_mean_7 = float(np.mean(lags[:7]))
        x_next = np.array(lags + [rolling_mean_7, dow], dtype=float).reshape(1, -1)

        yhat = float(model.predict(x_next)[0])
        yhat = max(yhat, 0.0)

        preds[d] = yhat
        series.loc[d] = yhat

    test_hist = test_hist.set_index("date")

    errors = []
    for d, row in test_hist.iterrows():
        y_true = float(row["qty"])
        y_pred = float(preds.get(d, 0.0))
        errors.append(abs(y_true - y_pred))

    if not errors:
        return None
    return float(np.mean(errors))


def build_db_url_from_env() -> str:
    """
    Option A: Use DB_URL directly.
    Option B: Build from Laravel-like env vars.
    """
    db_url = os.getenv("DB_URL")
    if db_url:
        return db_url

    driver = os.getenv("DB_CONNECTION", "mysql")
    host = os.getenv("DB_HOST", "127.0.0.1")
    port = os.getenv("DB_PORT", "3306")
    name = os.getenv("DB_DATABASE")
    user = os.getenv("DB_USERNAME")
    pwd  = os.getenv("DB_PASSWORD", "")

    if not all([name, user]):
        raise RuntimeError("Set DB_URL or DB_DATABASE/DB_USERNAME/DB_PASSWORD env vars.")

    if driver == "mysql":
        return f"mysql+pymysql://{user}:{pwd}@{host}:{port}/{name}"

    raise RuntimeError(f"Unsupported DB_CONNECTION={driver}. Use DB_URL instead.")


def main():
    horizon_days = int(os.getenv("FORECAST_HORIZON_DAYS", "7"))
    eval_days = int(os.getenv("EVAL_DAYS", "14"))

    db_url = build_db_url_from_env()
    engine = create_engine(db_url, pool_pre_ping=True)

    # 1) read sales
    sales_sql = """
        SELECT product_id, sale_date, sales_volume
        FROM sales_dailies
        ORDER BY product_id, sale_date
    """
    df = pd.read_sql(sales_sql, engine)
    if df.empty:
        print("No rows in sales_dailies.")
        return

    df = df.rename(columns={"sale_date": "date", "sales_volume": "qty"})
    df["date"] = pd.to_datetime(df["date"])

    # ===== Fill missing dates per product (ensure daily continuity) =====
    filled = []
    for pid, g in df.groupby("product_id"):
        g = g.sort_values("date").copy()
        full_range = pd.date_range(g["date"].min(), g["date"].max(), freq="D")

        g = g.set_index("date").reindex(full_range)
        g["product_id"] = pid
        g["qty"] = g["qty"].fillna(0)

        g = g.reset_index().rename(columns={"index": "date"})
        filled.append(g[["product_id", "date", "qty"]])

    df = pd.concat(filled, ignore_index=True)
    # ================================================================

    # 2) read current stock
    stock_sql = "SELECT product_id, current_stock FROM inventory_current"
    stock_df = pd.read_sql(stock_sql, engine)
    stock_map = dict(zip(stock_df["product_id"].astype(int), stock_df["current_stock"].astype(int)))

    rows_to_insert = []
    maes = []

    for pid, g in df.groupby("product_id"):
        pid_int = int(pid)
        g = g[["product_id", "date", "qty"]].copy()

        # mae14 = train_and_eval_mae(g, eval_days=eval_days)
        # if mae14 is not None:
        #     maes.append(mae14)
        #     print(f"product_id={pid_int} MAE_{eval_days}d={mae14:.2f}")
        mae14 = train_and_eval_mae(g, eval_days=eval_days)
        if mae14 is not None:
            maes.append(mae14)

        total_pred = train_and_forecast_sum(g, horizon_days=horizon_days)
        current_stock = int(stock_map.get(pid_int, 0))

        suggested = int(max(np.ceil(total_pred - current_stock), 0))

        rows_to_insert.append({
            "product_id": pid_int,
            "horizon_days": horizon_days,
            "predicted_demand": float(round(total_pred, 2)),
            "suggested_order_qty": suggested,
            "model_version": MODEL_VERSION
        })

    if maes:
        print(f"Overall MAE_{eval_days}d (avg over products with enough data): {float(np.mean(maes)):.2f}")

    if not rows_to_insert:
        print("No products to forecast.")
        return

    # 3) write into predictions (clean strategy)
    with engine.begin() as conn:
        conn.execute(text("""
            DELETE FROM predictions
            WHERE model_version = :mv
              AND DATE(generated_at) = CURDATE()
              AND horizon_days = :hd
        """), {"mv": MODEL_VERSION, "hd": horizon_days})

        conn.execute(text("""
            INSERT INTO predictions
            (product_id, horizon_days, predicted_demand, suggested_order_qty, model_version, generated_at, created_at, updated_at)
            VALUES
            (:product_id, :horizon_days, :predicted_demand, :suggested_order_qty, :model_version, NOW(), NOW(), NOW())
        """), rows_to_insert)

    print(f"Inserted {len(rows_to_insert)} rows into predictions with model_version={MODEL_VERSION}, horizon_days={horizon_days}.")


if __name__ == "__main__":
    main()
